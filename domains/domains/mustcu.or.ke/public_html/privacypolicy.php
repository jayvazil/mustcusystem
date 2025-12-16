<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Policies</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f0f4f8;
            background-image: linear-gradient(to bottom right, #f0f4f8, #e6eef5);
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            background-color: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(2, 7, 186, 0.1);
        }
        
        .header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(to right, #0207ba, #2c50e3);
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 2.2em;
            font-weight: 700;
        }
        
        .subheader {
            color: #7f8c8d;
            font-size: 1.2em;
        }
        
        .policy-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .policy-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            background-color: #f8fafc;
            padding: 18px 25px;
            border-bottom: 1px solid #eaeef3;
            display: flex;
            align-items: center;
        }
        
        .card-icon {
            margin-right: 20px;
            font-size: 26px;
            color: #0207ba;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(2, 7, 186, 0.1);
            border-radius: 50%;
            box-shadow: 0 3px 6px rgba(2, 7, 186, 0.1);
        }
        
        .card-title {
            color: #0207ba;
            font-size: 1.4em;
            margin: 0;
            font-weight: 600;
        }
        
        .card-content {
            padding: 25px;
        }
        
        .card-content p {
            margin-bottom: 18px;
            line-height: 1.7;
        }
        
        .card-content p:last-child {
            margin-bottom: 0;
        }
        
        ul {
            margin-left: 20px;
            margin-bottom: 18px;
            list-style-type: none;
        }
        
        ul li {
            margin-bottom: 10px;
            position: relative;
            padding-left: 28px;
        }
        
        ul li::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #0207ba;
            font-weight: bold;
            background-color: rgba(2, 7, 186, 0.08);
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8em;
        }
        
        .highlight {
            background-color: #f1f8ff;
            padding: 20px;
            border-left: 4px solid #ff7900;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
            box-shadow: 0 3px 8px rgba(255, 121, 0, 0.1);
        }
        
        .section-divider {
            text-align: center;
            margin: 50px 0 30px;
            position: relative;
        }
        
        .section-divider::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 1px;
            background: linear-gradient(to right, transparent, #d0d9e5, transparent);
            z-index: -1;
        }
        
        .section-divider span {
            background: #f0f4f8;
            padding: 0 25px;
            color: #0207ba;
            font-size: 1.3em;
            font-weight: 600;
            box-shadow: 0 0 15px #f0f4f8;
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            font-size: 0.9em;
            color: #7f8c8d;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        strong {
            color: #0207ba;
            font-weight: 600;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .header,
            .policy-card,
            .consent-form {
                padding: 15px;
            }
            
            h1 {
                font-size: 1.6em;
            }
            
            .card-header {
                padding: 12px 15px;
            }
            
            .card-title {
                font-size: 1.2em;
            }
        }
        
        /* New decorative elements */
        .header::after {
            content: "";
            position: absolute;
            bottom: 0;
            right: 0;
            width: 120px;
            height: 120px;
            background-color: rgba(2, 7, 186, 0.03);
            border-radius: 50%;
            transform: translate(30%, 30%);
            z-index: 0;
        }
        
        .policy-card::after {
            content: "";
            position: absolute;
            bottom: -10px;
            right: -10px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(2, 7, 186, 0.03);
            z-index: -1;
        }
        
        /* Additional decorative touches */
        .container::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 10% 10%, rgba(2, 7, 186, 0.03) 0%, transparent 8%),
                radial-gradient(circle at 90% 30%, rgba(255, 121, 0, 0.02) 0%, transparent 8%),
                radial-gradient(circle at 20% 60%, rgba(2, 7, 186, 0.03) 0%, transparent 10%),
                radial-gradient(circle at 80% 80%, rgba(255, 121, 0, 0.02) 0%, transparent 10%);
            z-index: -1;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Member Terms and Policies</h1>
            <p class="subheader">Please review our Photo Consent Policy and data handling policies</p>
        </div>

        <!-- DATA HANDLING AND PRIVACY POLICY SECTION -->
        <div class="section-divider">
            <span>Data Handling and Privacy Policy</span>
        </div>
        
        <div class="policy-card">
            <div class="card-header">
                <div class="card-icon">📋</div>
                <h2 class="card-title">1. Scope of This Policy</h2>
            </div>
            <div class="card-content">
                <p>This policy applies to:</p>
                <ul>
                    <li>All personal data collected from members via the  Christian Union  website, forms, emails, events, and digital tools.</li>
                    <li>Data shared voluntarily by members during registration, participation in events, communication with the Christian Union, or while using member-exclusive services.</li>
                </ul>
            </div>
        </div>
        
        <div class="policy-card">
            <div class="card-header">
                <div class="card-icon">📊</div>
                <h2 class="card-title">2. What Data We Collect</h2>
            </div>
            <div class="card-content">
                <p>We may collect the following categories of personal data from our members:</p>
                
                <p><strong>a) Identification Information</strong></p>
                <ul>
                    <li>Full name</li>
                    <li>Gender (optional)</li>
                    
                </ul>
                
                <p><strong>b) Contact Information</strong></p>
                <ul>
                    <li>Email address</li>
                    <li>Phone number</li>
                    
                </ul>
                
                <p><strong>c) Membership Information</strong></p>
                <ul>
                    <li>Membership ID or registration number</li>
                    <li>Date of registration</li>
                    <li>Membership type and status</li>
                    <li>Roles held in the Christian Union</li>
                </ul>
                
                <p><strong>d) Academic/Professional Details</strong></p>
                <ul>
                    <li>School, institution</li>
                    <li>Program of study </li>
                    
                </ul>
                
                <p><strong>e) Media Content</strong></p>
                <ul>
                    
                    <li>Group or event photos</li>
                    <li>Video content (e.g., recordings of webinars or events)</li>
                </ul>
                
               
                
                <p><strong>g) Other Voluntary Information</strong></p>
                <ul>
                    <li>Feedback or survey responses</li>
                    <li>Messages or inquiries</li>
                    <li>Uploaded documents (e.g. photos)</li>
                </ul>
            </div>
        </div>
        
        <div class="policy-card">
            <div class="card-header">
                <div class="card-icon">🔍</div>
                <h2 class="card-title">3. How We Use Your Data</h2>
            </div>
            <div class="card-content">
                <p>We use member data for the following legitimate purposes:</p>
                <ul>
                    <li>To register, verify, and manage memberships</li>
                    <li>To communicate  Christian Union updates, events, and announcements</li>
                    <li>To share event invites, newsletters, and surveys</li>
                    <li>To display member contributions or achievements (with consent)</li>
                    <li>To create directories, attendance lists, or certificates</li>
                    <li>To respond to inquiries or support requests</li>
                    <li>To personalize user experience on the website</li>
                    <li>To improve our services and digital platforms</li>
                    <li>To fulfill legal or regulatory obligations</li>
                </ul>
            </div>
        </div>
        
        <div class="policy-card">
            <div class="card-header">
                <div class="card-icon">⚖️</div>
                <h2 class="card-title">4. Legal Basis for Processing Data</h2>
            </div>
            <div class="card-content">
                <p>Our data handling practices are guided by the following lawful bases:</p>
                <ul>
                    <li><strong>Consent:</strong> You have voluntarily agreed to our use of your personal data for specific purposes.</li>
                    <li><strong>Legitimate Interests:</strong> We may process your data in ways necessary for the functioning and growth of the  Christian Union, provided your privacy rights are not harmed.</li>
                    <li><strong>Legal Obligation:</strong> We may be required to retain or disclose data by law.</li>
                </ul>
            </div>
        </div>
        
        <div class="policy-card">
            <div class="card-header">
                <div class="card-icon">🔄</div>
                <h2 class="card-title">5. Data Sharing and Disclosure</h2>
            </div>
            <div class="card-content">
                <p>We do not sell, rent, or trade your data. Data may be shared only in the following circumstances:</p>
                <ul>
                    <li>With authorized internal staff and volunteers for administrative purposes</li>
                    <li>With trusted third-party service providers (e.g., web hosts, email platforms), under strict confidentiality agreements</li>
                    <li>With event partners or collaborators, only with prior consent</li>
                    <li>When required by law, subpoena, or legal process</li>
                    <li>With your explicit permission for public recognition or publication</li>
                </ul>
            </div>
        </div>
        
        <div class="policy-card">
            <div class="card-header">
                <div class="card-icon">📸</div>
                <h2 class="card-title">6. Use of Photos and Media</h2>
            </div>
            <div class="card-content">
                <p>Member photos and videos taken during events or submitted for use may be published on:</p>
                <ul>
                    <li>The official website</li>
                    <li>Social media platforms</li>
                    <li>Newsletters, brochures, or Cu Posters</li>
                </ul>
                <p>You may opt out at any time as described in the Photo Consent Policy section.</p>
            </div>
        </div>
        
        <div class="policy-card">
            <div class="card-header">
                <div class="card-icon">🔒</div>
                <h2 class="card-title">7. Data Storage and Security Measures</h2>
            </div>
            <div class="card-content">
                <p>We take data protection seriously and use appropriate technical  safeguards, including:</p>
                <ul>
                    <li>Secure password-protected systems</li>
                    <li>Encrypted data transmissions (SSL/TLS)</li>
                    <li>Access control for sensitive files</li>
                    <li>Minimization of data access to only what is necessary</li>
                </ul>
                <p>Despite our efforts, no system is 100% secure, and we encourage you to contact us if you believe your data has been compromised.</p>
            </div>
        </div>
        
        <div class="policy-card">
            <div class="card-header">
                <div class="card-icon">⏱️</div>
                <h2 class="card-title">8. Data Retention Policy</h2>
            </div>
            <div class="card-content">
                <p>We retain personal data for associates and registered members only as long as:</p>
                <ul>
                    <li>You maintain your registered membership status with the Christian Union</li>
                    <li>Your associate account remains active in our system</li>
                    <li>It is needed for legitimate Christian Union purposes related to your membership</li>
                    <li>It is required by law or policy (e.g., financial or audit records)</li>
                </ul>
                <p>After your membership, your data will be securely stored in our system as an associate.</p>
            </div>
        </div>
        
        <div class="policy-card">
            <div class="card-header">
                <div class="card-icon">👤</div>
                <h2 class="card-title">9. Your Data Rights</h2>
            </div>
            <div class="card-content">
                <p>As a member, you have the right to:</p>
                <ul>
                    <li>Access your personal data</li>
                    <li>Correct or update inaccurate or outdated information</li>
                    <li>Withdraw consent at any time</li>
                    <li>Request deletion of your data ("right to be forgotten")</li>
                    <li>Request a copy of your data ("data portability")</li>
                    <li>Restrict or object to specific types of processing</li>
                    <li>Complain to a data protection authority if you believe your rights have been violated</li>
                </ul>
                <p>To exercise any of these rights, contact us using the details provided in the Contact Information section.</p>
            </div>
        </div>
        
        <div class="policy-card">
            <div class="card-header">
                <div class="card-icon">🍪</div>
                <h2 class="card-title">10. Cookies and Tracking</h2>
            </div>
            <div class="card-content">
                <p>Our website may use cookies or similar technologies to improve your experience. Cookies help us:</p>
                <ul>
                    <li>Recognize returning visitors</li>
                    <li>Analyze website traffic and usage</li>
                    <li>Improve performance and personalization</li>
                </ul>
                <p>You can manage or disable cookies in your browser settings.</p>
            </div>
        </div>
        
        <!-- PHOTO CONSENT POLICY SECTION -->
        <div class="section-divider">
            <span>Photo Consent Policy</span>
        </div>
        
        <div class="policy-card">
            <div class="card-header">
                <div class="card-icon">📸</div>
                <h2 class="card-title">1. Use of Photos</h2>
            </div>
            <div class="card-content">
                <p>As part of our efforts to showcase and celebrate the activities, achievements, and community spirit of our  Christian Union, we may use photos of members in the following ways:</p>
                <ul>
                    <li>Posts, stories, and highlights on our official social media platforms (e.g., Facebook, Instagram, Twitter, LinkedIn, etc.)</li>
                    <li>Galleries, banners, or feature articles on our official website</li>
                    <li>Promotional materials such as newsletters, event posters, or annual reports (digital and printed)</li>
                    <li>Media presentations or recaps of events for internal and external sharing</li>
                </ul>
                <p>These photos help us promote engagement, document memories, and represent the vibrant life of our Christian Union.</p>
            </div>
        </div>
        
        <div class="policy-card">
            <div class="card-header">
                <div class="card-icon">🤝</div>
                <h2 class="card-title">2. Implied Consent Upon Membership</h2>
            </div>
            <div class="card-content">
                <p>By choosing to become a member and by participating in MUST CU-related activities and events, you grant implied consent for your photograph or video image to be taken and used for the purposes listed above.</p>
                <p>This means that during events, meetings, or activities, official photographers or social media team members may capture moments that include your image, and those images may be published without requiring individual permission each time.</p>
                <p>However, we do this with full respect for your dignity and privacy (see point 3 and 4).</p>
            </div>
        </div>
        
        <div class="policy-card">
            <div class="card-header">
                <div class="card-icon">⭐</div>
                <h2 class="card-title">3. Respect and Integrity</h2>
            </div>
            <div class="card-content">
                <p>We are committed to using photos in a way that reflects our core values of respect, inclusion, and professionalism:</p>
                <ul>
                    <li>We will only publish images that are respectful, appropriate, and aligned with the context of the event or activity.</li>
                    <li>We will not use any photo in a manner that could be considered offensive, embarrassing, or misleading to the individuals featured.</li>
                    <li>Photos will not be manipulated or used in any way that changes the original intent or misrepresents the subject.</li>
                </ul>
            </div>
        </div>
        
        <div class="policy-card">
            <div class="card-header">
                <div class="card-icon">🚫</div>
                <h2 class="card-title">4. Opt-Out Option</h2>
            </div>
            <div class="card-content">
                <p>We fully understand and respect that not all members may wish to have their photos used. If you would prefer not to be included in any photographs or publications, you can <strong>opt out</strong> at any time by:</p>
                <ul>
                    <li>Informing us in writing or by email</li>
                   
                </ul>
                <p>Upon receiving your request, we will:</p>
                <ul>
                    
                    <li>Refrain from using existing images where you are clearly identifiable</li>
                    <li>Remove or blur your image from published content if possible</li>
                </ul>
                <p>We will always do our best to honor your request promptly and respectfully.</p>
            </div>
        </div>
        
        <div class="policy-card">
            <div class="card-header">
                <div class="card-icon">🔒</div>
                <h2 class="card-title">5. Privacy and Data Protection</h2>
            </div>
            <div class="card-content">
                <ul>
                    <li>We will not share or publish personal or sensitive information (e.g., phone number, student ID, address) alongside your photo without your explicit consent.</li>
                    <li>Group photos taken  in our Christian Union events may be used, but we will still honor requests for removal or non-use of individual images.</li>
                    <li>Photos will be stored securely and only accessed by authorized individuals responsible for communications and media.</li>
                </ul>
            </div>
        </div>
        
        
        
        <div class="policy-card">
            <div class="card-header">
                <div class="card-icon">📝</div>
                <h2 class="card-title">6. Policy Updates</h2>
            </div>
            <div class="card-content">
                <p>This policy may be reviewed and updated from time to time to reflect changes in technology, legal requirements, or Christian Union practices. Members will be notified of significant updates, and your continued participation will be understood as acceptance of the updated terms unless you explicitly opt out.</p>
            </div>
        </div>
        
        <div class="policy-card">
            <div class="card-header">
                <div class="card-icon">📞</div>
                <h2 class="card-title">7. Contact Information</h2>
            </div>
            <div class="card-content">
                <p>For questions, concerns, or requests related to your data or to opt out of photo usage, please contact us:</p>
                <div class="highlight">
                    <p><strong>Meru University Christian Union</strong><br>
                    Data Protection Officer<br>
                    Email: <strong>info@mustcu.or.ke</strong><br>
                    Phone: +254 795 398942 <br>
                    
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>© 2025 MUST CU. All rights reserved.</p>
            <p>Last updated: April 11, 2025</p>
        </div>
    </div>
</body>
</html>