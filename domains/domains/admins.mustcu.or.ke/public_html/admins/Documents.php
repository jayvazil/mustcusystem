<?php
require_once 'includes/db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Folder Manager Class
class FolderManager {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createFolder($name, $parent_id, $type, $created_by) {
        try {
            $query = "INSERT INTO folders (name, parent_id, type, created_by, status) 
                      VALUES (:name, :parent_id, :type, :created_by, 'active')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":parent_id", $parent_id);
            $stmt->bindParam(":type", $type);
            $stmt->bindParam(":created_by", $created_by);
            if($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Folder creation error: " . $e->getMessage());
            return false;
        }
    }

    public function getFolderTree($parent_id = null) {
        if($parent_id === null) {
            $query = "SELECT f.*, a.name as creator_name 
                      FROM folders f
                      LEFT JOIN admins a ON f.created_by = a.id
                      WHERE f.parent_id IS NULL AND f.status = 'active'
                      ORDER BY f.name";
        } else {
            $query = "SELECT f.*, a.name as creator_name 
                      FROM folders f
                      LEFT JOIN admins a ON f.created_by = a.id
                      WHERE f.parent_id = :parent_id AND f.status = 'active'
                      ORDER BY f.name";
        }
        
        $stmt = $this->conn->prepare($query);
        if($parent_id !== null) {
            $stmt->bindParam(":parent_id", $parent_id);
        }
        $stmt->execute();
        
        $folders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($folders as &$folder) {
            $folder['children'] = $this->getFolderTree($folder['id']);
            $folder['has_children'] = count($folder['children']) > 0;
        }
        
        return $folders;
    }

    public function getFolderPath($folder_id) {
        $path = [];
        $current_id = $folder_id;
        
        while ($current_id !== null) {
            $query = "SELECT id, name, parent_id FROM folders WHERE id = :id AND status = 'active'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $current_id);
            $stmt->execute();
            $folder = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($folder) {
                array_unshift($path, $folder);
                $current_id = $folder['parent_id'];
            } else {
                break;
            }
        }
        
        return $path;
    }

    public function deleteFolder($id) {
        // Also mark all child folders as deleted
        $this->deleteChildFolders($id);
        
        $query = "UPDATE folders SET status = 'deleted' WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    private function deleteChildFolders($parent_id) {
        $query = "SELECT id FROM folders WHERE parent_id = :parent_id AND status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":parent_id", $parent_id);
        $stmt->execute();
        $children = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($children as $child) {
            $this->deleteChildFolders($child['id']);
            $update = "UPDATE folders SET status = 'deleted' WHERE id = :id";
            $update_stmt = $this->conn->prepare($update);
            $update_stmt->bindParam(":id", $child['id']);
            $update_stmt->execute();
        }
    }

    public function getFolderById($id) {
        $query = "SELECT * FROM folders WHERE id = :id AND status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Document Manager Class
class DocumentManager {
    private $conn;
    private $upload_dir = "uploads/documents/";

    public function __construct($db) {
        $this->conn = $db;
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
    }

    public function uploadDocument($folder_id, $title, $description, $file, $uploaded_by) {
        $allowed_types = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip'];
        $file_type = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        
        if (!in_array($file_type, $allowed_types)) {
            return ["success" => false, "message" => "Invalid file type"];
        }
        
        $unique_name = uniqid() . '_' . basename($file["name"]);
        $target_file = $this->upload_dir . $unique_name;
        
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            $query = "INSERT INTO documents (folder_id, title, description, file_name, file_path, file_type, file_size, uploaded_by) 
                      VALUES (:folder_id, :title, :description, :file_name, :file_path, :file_type, :file_size, :uploaded_by)";
            
            $stmt = $this->conn->prepare($query);
            $file_name = basename($file["name"]);
            $file_size = $file["size"];
            $mime_type = mime_content_type($target_file);
            
            $stmt->bindParam(":folder_id", $folder_id);
            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":description", $description);
            $stmt->bindParam(":file_name", $file_name);
            $stmt->bindParam(":file_path", $target_file);
            $stmt->bindParam(":file_type", $mime_type);
            $stmt->bindParam(":file_size", $file_size);
            $stmt->bindParam(":uploaded_by", $uploaded_by);
            
            if($stmt->execute()) {
                return ["success" => true, "message" => "Document uploaded successfully"];
            }
        }
        return ["success" => false, "message" => "Failed to upload document"];
    }

    public function getDocumentsByFolder($folder_id) {
        $query = "SELECT d.*, a.name as uploader_name 
                  FROM documents d
                  LEFT JOIN admins a ON d.uploaded_by = a.id
                  WHERE d.folder_id = :folder_id AND d.status = 'active'";
        
        if ($_SESSION['role'] !== 'super_admin') {
            $query .= " AND d.uploaded_by = :uploaded_by";
        }
        
        $query .= " ORDER BY d.uploaded_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":folder_id", $folder_id);
        
        if ($_SESSION['role'] !== 'super_admin') {
            $uploaded_by = $_SESSION['admin_id'];
            $stmt->bindParam(":uploaded_by", $uploaded_by);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteDocument($id) {
        if ($_SESSION['role'] !== 'super_admin') {
            $check_query = "SELECT uploaded_by FROM documents WHERE id = :id";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(":id", $id);
            $check_stmt->execute();
            $doc = $check_stmt->fetch(PDO::FETCH_ASSOC);
            if ($doc['uploaded_by'] != $_SESSION['admin_id']) {
                return false;
            }
        }

        $query = "UPDATE documents SET status = 'deleted' WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}

// Photo Manager Class
class PhotoManager {
    private $conn;
    private $upload_dir = "uploads/photos/";
    private $thumb_dir = "uploads/thumbnails/";

    public function __construct($db) {
        $this->conn = $db;
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
        if (!file_exists($this->thumb_dir)) {
            mkdir($this->thumb_dir, 0777, true);
        }
    }

    public function uploadPhoto($folder_id, $title, $description, $file, $uploaded_by) {
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_type = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        
        if (!in_array($file_type, $allowed_types)) {
            return ["success" => false, "message" => "Invalid image type"];
        }
        
        $unique_name = uniqid() . '_' . basename($file["name"]);
        $target_file = $this->upload_dir . $unique_name;
        
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            list($width, $height) = getimagesize($target_file);
            $thumb_path = $this->createThumbnail($target_file, $unique_name);
            
            $query = "INSERT INTO photos (folder_id, title, description, file_name, file_path, thumbnail_path, file_size, width, height, uploaded_by) 
                      VALUES (:folder_id, :title, :description, :file_name, :file_path, :thumbnail_path, :file_size, :width, :height, :uploaded_by)";
            
            $stmt = $this->conn->prepare($query);
            $file_name = basename($file["name"]);
            $file_size = $file["size"];
            
            $stmt->bindParam(":folder_id", $folder_id);
            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":description", $description);
            $stmt->bindParam(":file_name", $file_name);
            $stmt->bindParam(":file_path", $target_file);
            $stmt->bindParam(":thumbnail_path", $thumb_path);
            $stmt->bindParam(":file_size", $file_size);
            $stmt->bindParam(":width", $width);
            $stmt->bindParam(":height", $height);
            $stmt->bindParam(":uploaded_by", $uploaded_by);
            
            if($stmt->execute()) {
                return ["success" => true, "message" => "Photo uploaded successfully"];
            }
        }
        return ["success" => false, "message" => "Failed to upload photo"];
    }

    private function createThumbnail($source, $filename) {
        $thumb_width = 300;
        $thumb_height = 300;
        
        list($width, $height) = getimagesize($source);
        $ratio = min($thumb_width / $width, $thumb_height / $height);
        $new_width = $width * $ratio;
        $new_height = $height * $ratio;
        
        $thumb = imagecreatetruecolor($new_width, $new_height);
        $source_image = imagecreatefromstring(file_get_contents($source));
        
        imagecopyresampled($thumb, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        
        $thumb_path = $this->thumb_dir . 'thumb_' . $filename;
        imagejpeg($thumb, $thumb_path, 85);
        
        imagedestroy($thumb);
        imagedestroy($source_image);
        
        return $thumb_path;
    }

    public function getPhotosByFolder($folder_id) {
        $query = "SELECT p.*, a.name as uploader_name 
                  FROM photos p
                  LEFT JOIN admins a ON p.uploaded_by = a.id
                  WHERE p.folder_id = :folder_id AND p.status = 'active'";
        
        if ($_SESSION['role'] !== 'super_admin') {
            $query .= " AND p.uploaded_by = :uploaded_by";
        }
        
        $query .= " ORDER BY p.uploaded_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":folder_id", $folder_id);
        
        if ($_SESSION['role'] !== 'super_admin') {
            $uploaded_by = $_SESSION['admin_id'];
            $stmt->bindParam(":uploaded_by", $uploaded_by);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deletePhoto($id) {
        if ($_SESSION['role'] !== 'super_admin') {
            $check_query = "SELECT uploaded_by FROM photos WHERE id = :id";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(":id", $id);
            $check_stmt->execute();
            $photo = $check_stmt->fetch(PDO::FETCH_ASSOC);
            if ($photo['uploaded_by'] != $_SESSION['admin_id']) {
                return false;
            }
        }

        $query = "UPDATE photos SET status = 'deleted' WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}

// Handle AJAX Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    $database = new Database();
    $db = $database->getConnection();
    
    $action = $_POST['action'] ?? '';
    
    if(!isset($_SESSION['admin_id'])) {
        $_SESSION['admin_id'] = 1;
        $_SESSION['role'] = 'admin';
    }
    
    switch($action) {
        case 'create_folder':
            $folderManager = new FolderManager($db);
            $result = $folderManager->createFolder(
                $_POST['name'],
                $_POST['parent_id'] ?: null,
                $_POST['type'],
                $_SESSION['admin_id']
            );
            echo json_encode(["success" => $result !== false, "folder_id" => $result]);
            break;
            
        case 'get_folders':
            $folderManager = new FolderManager($db);
            $folders = $folderManager->getFolderTree();
            echo json_encode($folders);
            break;

        case 'get_folder_path':
            $folderManager = new FolderManager($db);
            $path = $folderManager->getFolderPath($_POST['folder_id']);
            echo json_encode($path);
            break;

        case 'get_folder':
            $folderManager = new FolderManager($db);
            $folder = $folderManager->getFolderById($_POST['folder_id']);
            echo json_encode($folder);
            break;
            
        case 'delete_folder':
            $folderManager = new FolderManager($db);
            $result = $folderManager->deleteFolder($_POST['folder_id']);
            echo json_encode(["success" => $result]);
            break;
            
        case 'upload_document':
            $docManager = new DocumentManager($db);
            $result = $docManager->uploadDocument(
                $_POST['folder_id'],
                $_POST['title'],
                $_POST['description'],
                $_FILES['document'],
                $_SESSION['admin_id']
            );
            echo json_encode($result);
            break;
            
        case 'get_documents':
            $docManager = new DocumentManager($db);
            $documents = $docManager->getDocumentsByFolder($_POST['folder_id']);
            echo json_encode($documents);
            break;
            
        case 'delete_document':
            $docManager = new DocumentManager($db);
            $result = $docManager->deleteDocument($_POST['document_id']);
            echo json_encode(["success" => $result]);
            break;
            
        case 'upload_photo':
            $photoManager = new PhotoManager($db);
            $result = $photoManager->uploadPhoto(
                $_POST['folder_id'],
                $_POST['title'],
                $_POST['description'],
                $_FILES['photo'],
                $_SESSION['admin_id']
            );
            echo json_encode($result);
            break;
            
        case 'get_photos':
            $photoManager = new PhotoManager($db);
            $photos = $photoManager->getPhotosByFolder($_POST['folder_id']);
            echo json_encode($photos);
            break;
            
        case 'delete_photo':
            $photoManager = new PhotoManager($db);
            $result = $photoManager->deletePhoto($_POST['photo_id']);
            echo json_encode(["success" => $result]);
            break;
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hereditary Document & Photo Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-blue: #0207BA;
            --primary-orange: #FF7900;
            --primary-yellow: #FFF000;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #0408d4 100%);
            color: white;
            padding: 20px 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }

        .header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .container {
            display: flex;
            max-width: 1600px;
            margin: 30px auto;
            padding: 0 30px;
            gap: 30px;
        }

        .sidebar {
            width: 320px;
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            height: fit-content;
            max-height: calc(100vh - 160px);
            overflow-y: auto;
        }

        .sidebar h2 {
            color: var(--primary-blue);
            margin-bottom: 20px;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary-orange);
            color: white;
        }

        .btn-primary:hover {
            background: #e66d00;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 121, 0, 0.3);
        }

        .btn-secondary {
            background: var(--primary-blue);
            color: white;
        }

        .btn-secondary:hover {
            background: #0106a0;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(2, 7, 186, 0.3);
        }

        .btn-warning {
            background: var(--primary-yellow);
            color: var(--primary-blue);
        }

        .btn-warning:hover {
            background: #f0e600;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
        }

        .folder-tree {
            margin-top: 15px;
        }

        .folder-item {
            padding: 10px;
            margin: 5px 0;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            position: relative;
        }

        .folder-item:hover {
            background: #f0f4ff;
        }

        .folder-item.active {
            background: var(--primary-blue);
            color: white;
        }

        .folder-item.subfolder {
            margin-left: 20px;
        }

        .folder-icon {
            font-size: 18px;
            min-width: 20px;
        }

        .folder-toggle {
            cursor: pointer;
            margin-right: 5px;
            font-size: 14px;
            user-select: none;
            min-width: 15px;
            text-align: center;
        }

        .folder-children {
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .folder-children.collapsed {
            max-height: 0;
        }

        .folder-children.expanded {
            max-height: 2000px;
        }

        .folder-actions {
            margin-left: auto;
            display: none;
            gap: 5px;
        }

        .folder-item:hover .folder-actions {
            display: flex;
        }

        .folder-action-btn {
            padding: 2px 8px;
            font-size: 11px;
            background: rgba(255,255,255,0.9);
            color: var(--primary-blue);
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .folder-action-btn:hover {
            background: white;
        }

        .folder-item.active .folder-action-btn {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .folder-item.active .folder-action-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .main-content {
            flex: 1;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 3px solid var(--primary-yellow);
        }

        .content-header h2 {
            color: var(--primary-blue);
            font-size: 24px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .tab-container {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            border-bottom: 2px solid #e0e0e0;
        }

        .tab {
            padding: 12px 24px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            color: #666;
            transition: all 0.3s ease;
        }

        .tab.active {
            color: var(--primary-blue);
            border-bottom-color: var(--primary-orange);
        }

        .tab:hover {
            color: var(--primary-blue);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .card:hover {
            border-color: var(--primary-orange);
            box-shadow: 0 8px 20px rgba(255, 121, 0, 0.15);
            transform: translateY(-5px);
        }

        .card-icon {
            font-size: 48px;
            margin-bottom: 15px;
            color: var(--primary-blue);
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .card-info {
            font-size: 13px;
            color: #666;
            margin-bottom: 15px;
        }

        .card-actions {
            display: flex;
            gap: 8px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--primary-yellow);
        }

        .modal-header h3 {
            color: var(--primary-blue);
            font-size: 22px;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #999;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-modal:hover {
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-blue);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .photo-card {
            position: relative;
            overflow: hidden;
        }

        .photo-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
            flex-wrap: wrap;
        }

        .breadcrumb a {
            color: var(--primary-blue);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .breadcrumb-separator {
            color: #999;
        }

        .folder-path-select {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 13px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📁 Hereditary Document & Photo Management System</h1>
        <p>Advanced Nested Folder Organization Platform</p>
    </div>

    <div class="container">
        <div class="sidebar">
            <h2>
                <span>📂</span> Folders
            </h2>
            <button class="btn btn-primary" style="width: 100%; margin-bottom: 15px;" onclick="openFolderModal(null)">
                ➕ New Root Folder
            </button>
            <div id="folderTree" class="folder-tree">
                <!-- Folders will be loaded here -->
            </div>
        </div>

        <div class="main-content">
            <div class="breadcrumb" id="breadcrumb">
                <a href="#" onclick="loadRootFolder(); return false;">🏠 Home</a>
            </div>

            <div class="content-header">
                <h2 id="contentTitle">All Files</h2>
                <div class="action-buttons">
                    <button class="btn btn-secondary" onclick="openUploadModal('document')">
                        📄 Upload Document
                    </button>
                    <button class="btn btn-warning" onclick="openUploadModal('photo')">
                        🖼️ Upload Photo
                    </button>
                </div>
            </div>

            <div class="tab-container">
                <button class="tab active" onclick="switchTab('documents')">Documents</button>
                <button class="tab" onclick="switchTab('photos')">Photos</button>
            </div>

            <div id="alert-container"></div>

            <div id="documentsContent" class="grid">
                <!-- Documents will be loaded here -->
            </div>

            <div id="photosContent" class="grid" style="display: none;">
                <!-- Photos will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Create Folder Modal -->
    <div id="folderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="folderModalTitle">Create New Folder</h3>
                <button class="close-modal" onclick="closeFolderModal()">×</button>
            </div>
            <form id="folderForm" onsubmit="createFolder(event)">
                <div class="folder-path-select" id="folderPathDisplay" style="display: none;">
                    Creating in: <strong id="parentFolderPath"></strong>
                </div>
                <div class="form-group">
                    <label>Folder Name *</label>
                    <input type="text" name="name" required placeholder="Enter folder name">
                </div>
                <div class="form-group">
                    <label>Folder Type *</label>
                    <select name="type" required>
                        <option value="mixed">Mixed (Documents & Photos)</option>
                        <option value="document">Documents Only</option>
                        <option value="photo">Photos Only</option>
                    </select>
                </div>
                <input type="hidden" name="parent_id" id="parentFolderId">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Create Folder</button>
            </form>
        </div>
    </div>

    <!-- Upload Document Modal -->
    <div id="uploadDocumentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Upload Document</h3>
                <button class="close-modal" onclick="closeUploadModal('document')">×</button>
            </div>
            <form id="documentForm" onsubmit="uploadDocument(event)">
                <div class="form-group">
                    <label>Document Title *</label>
                    <input type="text" name="title" required placeholder="Enter document title">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Optional description"></textarea>
                </div>
                <div class="form-group">
                    <label>Select File * (PDF, DOC, DOCX, XLS, XLSX, TXT, ZIP)</label>
                    <input type="file" name="document" required accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.zip">
                </div>
                <input type="hidden" name="folder_id" id="docFolderId">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Upload Document</button>
            </form>
        </div>
    </div>

    <!-- Upload Photo Modal -->
    <div id="uploadPhotoModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Upload Photo</h3>
                <button class="close-modal" onclick="closeUploadModal('photo')">×</button>
            </div>
            <form id="photoForm" onsubmit="uploadPhoto(event)">
                <div class="form-group">
                    <label>Photo Title *</label>
                    <input type="text" name="title" required placeholder="Enter photo title">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Optional description"></textarea>
                </div>
                <div class="form-group">
                    <label>Select Image * (JPG, PNG, GIF, WEBP)</label>
                    <input type="file" name="photo" required accept="image/*">
                </div>
                <input type="hidden" name="folder_id" id="photoFolderId">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Upload Photo</button>
            </form>
        </div>
    </div>

    <script>
        let currentFolderId = null;
        let currentTab = 'documents';
        let expandedFolders = new Set();
        let allFoldersData = [];

        // Load folders on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadFolders();
            loadContent();
        });

        // Load folder tree
        function loadFolders() {
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'ajax=1&action=get_folders'
            })
            .then(response => response.json())
            .then(folders => {
                allFoldersData = folders;
                displayFolders(folders, document.getElementById('folderTree'));
            })
            .catch(error => console.error('Error:', error));
        }

        // Display folders recursively with collapse/expand
        function displayFolders(folders, container, level = 0) {
            container.innerHTML = '';
            folders.forEach(folder => {
                const folderWrapper = document.createElement('div');
                
                const folderDiv = document.createElement('div');
                folderDiv.className = 'folder-item' + (level > 0 ? ' subfolder' : '');
                folderDiv.setAttribute('data-folder-id', folder.id);
                
                const hasChildren = folder.children && folder.children.length > 0;
                const isExpanded = expandedFolders.has(folder.id);
                
                folderDiv.innerHTML = `
                    ${hasChildren ? `<span class="folder-toggle" onclick="toggleFolder(${folder.id}, event)">${isExpanded ? '▼' : '▶'}</span>` : '<span style="width: 15px; display: inline-block;"></span>'}
                    <span class="folder-icon">${getFolderIcon(folder.type)}</span>
                    <span onclick="selectFolder(${folder.id}, '${escapeHtml(folder.name)}', event)">${escapeHtml(folder.name)}</span>
                    <span class="folder-actions">
                        <button class="folder-action-btn" onclick="openFolderModal(${folder.id}, event)">➕</button>
                        <button class="folder-action-btn" onclick="deleteFolder(${folder.id}, event)">🗑️</button>
                    </span>
                `;
                
                folderWrapper.appendChild(folderDiv);

                if (hasChildren) {
                    const childrenContainer = document.createElement('div');
                    childrenContainer.className = `folder-children ${isExpanded ? 'expanded' : 'collapsed'}`;
                    childrenContainer.setAttribute('data-parent-id', folder.id);
                    displayFolders(folder.children, childrenContainer, level + 1);
                    folderWrapper.appendChild(childrenContainer);
                }
                
                container.appendChild(folderWrapper);
            });
        }

        // Toggle folder expand/collapse
        function toggleFolder(folderId, event) {
            event.stopPropagation();
            
            const childrenContainer = document.querySelector(`.folder-children[data-parent-id="${folderId}"]`);
            const toggle = event.target;
            
            if (expandedFolders.has(folderId)) {
                expandedFolders.delete(folderId);
                childrenContainer.classList.remove('expanded');
                childrenContainer.classList.add('collapsed');
                toggle.textContent = '▶';
            } else {
                expandedFolders.add(folderId);
                childrenContainer.classList.remove('collapsed');
                childrenContainer.classList.add('expanded');
                toggle.textContent = '▼';
            }
        }

        // Get folder icon based on type
        function getFolderIcon(type) {
            switch(type) {
                case 'document': return '📄';
                case 'photo': return '🖼️';
                default: return '📁';
            }
        }

        // Select folder
        function selectFolder(folderId, folderName, event) {
            if (event) event.stopPropagation();
            
            currentFolderId = folderId;
            document.getElementById('contentTitle').textContent = folderName;
            
            // Update active state
            document.querySelectorAll('.folder-item').forEach(item => {
                item.classList.remove('active');
            });
            
            const selectedFolder = document.querySelector(`.folder-item[data-folder-id="${folderId}"]`);
            if (selectedFolder) {
                selectedFolder.classList.add('active');
            }
            
            // Update breadcrumb
            updateBreadcrumb(folderId);
            
            loadContent();
        }

        // Update breadcrumb navigation
        function updateBreadcrumb(folderId) {
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `ajax=1&action=get_folder_path&folder_id=${folderId}`
            })
            .then(response => response.json())
            .then(path => {
                const breadcrumb = document.getElementById('breadcrumb');
                breadcrumb.innerHTML = '<a href="#" onclick="loadRootFolder(); return false;">🏠 Home</a>';
                
                path.forEach((folder, index) => {
                    breadcrumb.innerHTML += '<span class="breadcrumb-separator">›</span>';
                    breadcrumb.innerHTML += `<a href="#" onclick="selectFolder(${folder.id}, '${escapeHtml(folder.name)}'); return false;">${escapeHtml(folder.name)}</a>`;
                });
            })
            .catch(error => console.error('Error:', error));
        }

        // Load content (documents or photos)
        function loadContent() {
            if (currentTab === 'documents') {
                loadDocuments();
            } else {
                loadPhotos();
            }
        }

        // Load documents
        function loadDocuments() {
            if (!currentFolderId) {
                document.getElementById('documentsContent').innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">📂</div>
                        <p>Select a folder to view documents</p>
                    </div>
                `;
                return;
            }

            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `ajax=1&action=get_documents&folder_id=${currentFolderId}`
            })
            .then(response => response.json())
            .then(documents => {
                const container = document.getElementById('documentsContent');
                
                if (documents.length === 0) {
                    container.innerHTML = `
                        <div class="empty-state">
                            <div class="empty-state-icon">📄</div>
                            <p>No documents in this folder</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = documents.map(doc => `
                    <div class="card">
                        <div class="card-icon">📄</div>
                        <div class="card-title">${escapeHtml(doc.title)}</div>
                        <div class="card-info">
                            ${escapeHtml(doc.description) || 'No description'}<br>
                            <small>Size: ${formatFileSize(doc.file_size)}</small><br>
                            <small>Uploaded by: ${escapeHtml(doc.uploader_name)}</small>
                        </div>
                        <div class="card-actions">
                            <a href="${doc.file_path}" download class="btn btn-secondary btn-small">Download</a>
                            <button onclick="deleteDocument(${doc.id})" class="btn btn-danger btn-small">Delete</button>
                        </div>
                    </div>
                `).join('');
            })
            .catch(error => console.error('Error:', error));
        }

        // Load photos
        function loadPhotos() {
            if (!currentFolderId) {
                document.getElementById('photosContent').innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">🖼️</div>
                        <p>Select a folder to view photos</p>
                    </div>
                `;
                return;
            }

            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `ajax=1&action=get_photos&folder_id=${currentFolderId}`
            })
            .then(response => response.json())
            .then(photos => {
                const container = document.getElementById('photosContent');
                
                if (photos.length === 0) {
                    container.innerHTML = `
                        <div class="empty-state">
                            <div class="empty-state-icon">🖼️</div>
                            <p>No photos in this folder</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = photos.map(photo => `
                    <div class="card photo-card">
                        <img src="${photo.thumbnail_path || photo.file_path}" alt="${escapeHtml(photo.title)}">
                        <div class="card-title">${escapeHtml(photo.title)}</div>
                        <div class="card-info">
                            ${escapeHtml(photo.description) || 'No description'}<br>
                            <small>${photo.width} × ${photo.height}</small><br>
                            <small>Uploaded by: ${escapeHtml(photo.uploader_name)}</small>
                        </div>
                        <div class="card-actions">
                            <a href="${photo.file_path}" target="_blank" class="btn btn-secondary btn-small">View</a>
                            <button onclick="deletePhoto(${photo.id})" class="btn btn-danger btn-small">Delete</button>
                        </div>
                    </div>
                `).join('');
            })
            .catch(error => console.error('Error:', error));
        }

        // Switch between tabs
        function switchTab(tab) {
            currentTab = tab;
            
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            event.currentTarget.classList.add('active');
            
            if (tab === 'documents') {
                document.getElementById('documentsContent').style.display = 'grid';
                document.getElementById('photosContent').style.display = 'none';
                loadDocuments();
            } else {
                document.getElementById('documentsContent').style.display = 'none';
                document.getElementById('photosContent').style.display = 'grid';
                loadPhotos();
            }
        }

        // Modal functions
        function openFolderModal(parentId = null, event = null) {
            if (event) event.stopPropagation();
            
            const modal = document.getElementById('folderModal');
            const pathDisplay = document.getElementById('folderPathDisplay');
            const parentFolderPath = document.getElementById('parentFolderPath');
            
            if (parentId) {
                document.getElementById('parentFolderId').value = parentId;
                document.getElementById('folderModalTitle').textContent = 'Create Subfolder';
                
                // Get and display parent folder path
                fetch('', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `ajax=1&action=get_folder_path&folder_id=${parentId}`
                })
                .then(response => response.json())
                .then(path => {
                    const pathStr = path.map(f => f.name).join(' › ');
                    parentFolderPath.textContent = pathStr;
                    pathDisplay.style.display = 'block';
                })
                .catch(error => console.error('Error:', error));
            } else {
                document.getElementById('parentFolderId').value = '';
                document.getElementById('folderModalTitle').textContent = 'Create New Root Folder';
                pathDisplay.style.display = 'none';
            }
            
            modal.classList.add('active');
        }

        function closeFolderModal() {
            document.getElementById('folderModal').classList.remove('active');
            document.getElementById('folderForm').reset();
        }

        function openUploadModal(type) {
            if (!currentFolderId) {
                showAlert('Please select a folder first', 'error');
                return;
            }
            
            if (type === 'document') {
                document.getElementById('docFolderId').value = currentFolderId;
                document.getElementById('uploadDocumentModal').classList.add('active');
            } else {
                document.getElementById('photoFolderId').value = currentFolderId;
                document.getElementById('uploadPhotoModal').classList.add('active');
            }
        }

        function closeUploadModal(type) {
            if (type === 'document') {
                document.getElementById('uploadDocumentModal').classList.remove('active');
                document.getElementById('documentForm').reset();
            } else {
                document.getElementById('uploadPhotoModal').classList.remove('active');
                document.getElementById('photoForm').reset();
            }
        }

        // Create folder
        function createFolder(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            formData.append('ajax', '1');
            formData.append('action', 'create_folder');

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Folder created successfully!', 'success');
                    closeFolderModal();
                    
                    // If creating a subfolder, expand parent
                    const parentId = document.getElementById('parentFolderId').value;
                    if (parentId) {
                        expandedFolders.add(parseInt(parentId));
                    }
                    
                    loadFolders();
                } else {
                    showAlert('Failed to create folder', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred', 'error');
            });
        }

        // Upload document
        function uploadDocument(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            formData.append('ajax', '1');
            formData.append('action', 'upload_document');

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Document uploaded successfully!', 'success');
                    closeUploadModal('document');
                    loadDocuments();
                } else {
                    showAlert(data.message || 'Failed to upload document', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred', 'error');
            });
        }

        // Upload photo
        function uploadPhoto(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            formData.append('ajax', '1');
            formData.append('action', 'upload_photo');

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Photo uploaded successfully!', 'success');
                    closeUploadModal('photo');
                    loadPhotos();
                } else {
                    showAlert(data.message || 'Failed to upload photo', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred', 'error');
            });
        }

        // Delete folder
        function deleteFolder(folderId, event) {
            if (event) event.stopPropagation();
            
            if (!confirm('Are you sure you want to delete this folder and all its contents?')) return;

            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `ajax=1&action=delete_folder&folder_id=${folderId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Folder deleted successfully!', 'success');
                    if (currentFolderId === folderId) {
                        loadRootFolder();
                    }
                    loadFolders();
                } else {
                    showAlert('Failed to delete folder', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred', 'error');
            });
        }

        // Delete document
        function deleteDocument(documentId) {
            if (!confirm('Are you sure you want to delete this document?')) return;

            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `ajax=1&action=delete_document&document_id=${documentId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Document deleted successfully!', 'success');
                    loadDocuments();
                } else {
                    showAlert('Failed to delete document', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred', 'error');
            });
        }

        // Delete photo
        function deletePhoto(photoId) {
            if (!confirm('Are you sure you want to delete this photo?')) return;

            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `ajax=1&action=delete_photo&photo_id=${photoId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Photo deleted successfully!', 'success');
                    loadPhotos();
                } else {
                    showAlert('Failed to delete photo', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred', 'error');
            });
        }

        // Show alert
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alert-container');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
            
            alertContainer.innerHTML = `<div class="alert ${alertClass}">${escapeHtml(message)}</div>`;
            
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 3000);
        }

        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        // Load root folder
        function loadRootFolder() {
            currentFolderId = null;
            document.getElementById('contentTitle').textContent = 'All Files';
            document.querySelectorAll('.folder-item').forEach(item => {
                item.classList.remove('active');
            });
            document.getElementById('breadcrumb').innerHTML = '<a href="#" onclick="loadRootFolder(); return false;">🏠 Home</a>';
            loadContent();
        }

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>