<?php
// Enable error reporting for debugging


require_once '../shared/config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'leader') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);

    if (!in_array($category, ['members', 'leaders', 'associates'])) {
        header('Location: Users.php?category=' . $category . '&error=invalid_category');
        exit;
    }

    $table = $category;

    // Collect fields
    $fields = [];
    $fields['name'] = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $fields['email'] = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $fields['phone'] = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);

    if ($category === 'members') {
        $fields['ministry'] = filter_input(INPUT_POST, 'ministry', FILTER_SANITIZE_STRING);
        $fields['year'] = filter_input(INPUT_POST, 'year', FILTER_SANITIZE_STRING);
        $fields['course'] = filter_input(INPUT_POST, 'course', FILTER_SANITIZE_STRING);
    } elseif ($category === 'leaders') {
        $fields['year'] = filter_input(INPUT_POST, 'year', FILTER_SANITIZE_STRING);
        $fields['course'] = filter_input(INPUT_POST, 'course', FILTER_SANITIZE_STRING);
        $fields['completion_year'] = filter_input(INPUT_POST, 'completion_year', FILTER_SANITIZE_STRING);
        $fields['ministry'] = filter_input(INPUT_POST, 'ministry', FILTER_SANITIZE_STRING);
        $fields['position'] = filter_input(INPUT_POST, 'position', FILTER_SANITIZE_STRING);
        $fields['docket'] = filter_input(INPUT_POST, 'docket', FILTER_SANITIZE_STRING);
    } elseif ($category === 'associates') {
        $fields['completion_year'] = filter_input(INPUT_POST, 'completion_year', FILTER_SANITIZE_STRING);
        $fields['ministry'] = filter_input(INPUT_POST, 'ministry', FILTER_SANITIZE_STRING);
        $fields['course'] = filter_input(INPUT_POST, 'course', FILTER_SANITIZE_STRING);
        $fields['position'] = filter_input(INPUT_POST, 'position', FILTER_SANITIZE_STRING);
        $fields['docket'] = filter_input(INPUT_POST, 'docket', FILTER_SANITIZE_STRING);
        $fields['previous_role'] = filter_input(INPUT_POST, 'previous_role', FILTER_SANITIZE_STRING);
    }

    // Build update query
    $set = [];
    $params = [];
    foreach ($fields as $key => $value) {
        if ($value !== null) { // Only update non-null values
            $set[] = "$key = :$key";
            $params[":$key"] = $value;
        }
    }

    if (empty($set)) {
        header('Location: Users.php?category=' . $category . '&error=no_changes');
        exit;
    }

    $query = "UPDATE $table SET " . implode(', ', $set) . " WHERE id = :id";
    $params[':id'] = $user_id;

    $stmt = $pdo->prepare($query);
    if ($stmt->execute($params)) {
        header('Location: Users.php?category=' . $category . '&success=updated');
    } else {
        header('Location: Users.php?category=' . $category . '&error=update_failed');
    }
    exit;
} else {
    header('Location: Users.php');
    exit;
}