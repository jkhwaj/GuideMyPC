<?php
$pageKey = basename(parse_url($_SERVER['SCRIPT_NAME'] ?? 'index.php', PHP_URL_PATH) ?: 'index.php');
$pageDetails = [
    'index.php' => ['GuideMyPC | Practical Tech Support', 'Clear, practical technology support for everyday devices and connections.'],
    'guides.php' => ['Guides | GuideMyPC', 'Browse step-by-step technology troubleshooting guides.'],
    'guide.php' => ['Repair Guide | GuideMyPC', 'Follow clear, safety-conscious troubleshooting steps.'],
    'downloads.php' => ['Trusted Downloads | GuideMyPC', 'Find official technology support and download resources.'],
    'community.php' => ['Community | GuideMyPC', 'Discuss practical technology support with the GuideMyPC community.'],
    'login.php' => ['Sign In | GuideMyPC', 'Sign in to save guide progress and favorites.'],
    'register.php' => ['Create Account | GuideMyPC', 'Create a GuideMyPC account to save your troubleshooting progress.'],
    'profile.php' => ['My Profile | GuideMyPC', 'Review your saved troubleshooting progress and favorites.'],
    'dashboard.php' => ['Dashboard | GuideMyPC', 'Review your GuideMyPC activity and role-appropriate content metrics.'],
    'admin.php' => ['Admin | GuideMyPC', 'Manage GuideMyPC content and community operations.'],
    'about.php' => ['About | GuideMyPC', 'Learn about GuideMyPC and its approach to practical technology support.'],
    'contact.php' => ['Contact | GuideMyPC', 'Find the current support options for GuideMyPC.'],
    'ai.php' => ['AI Assistant | GuideMyPC', 'Learn about the planned GuideMyPC AI troubleshooting assistant.'],
    'search.php' => ['Search Support | GuideMyPC', 'Search GuideMyPC troubleshooting guides by problem, device, or error.'],
    'knowledge.php' => ['Knowledge Base | GuideMyPC', 'Browse reviewed GuideMyPC technical explanations, FAQs, and error codes.'],
    'knowledge_article.php' => ['Knowledge Article | GuideMyPC', 'Read a reviewed GuideMyPC technical reference.'],
    'glossary.php' => ['Technology Glossary | GuideMyPC', 'Understand common technology support terms.'],
    'error-code.php' => ['Error Code Reference | GuideMyPC', 'Look up a reviewed GuideMyPC error code reference.'],
    'privacy.php' => ['Privacy | GuideMyPC', 'Read how GuideMyPC handles account and support information.'],
    'terms.php' => ['Terms | GuideMyPC', 'Read the terms for using GuideMyPC.'],
    'disclaimer.php' => ['Disclaimer | GuideMyPC', 'Understand the limits of GuideMyPC troubleshooting guidance.'],
    'donate.php' => ['Support GuideMyPC | GuideMyPC', 'Learn how to support the continued development of GuideMyPC.'],
    'forgot_password.php' => ['Reset Password | GuideMyPC', 'Request a GuideMyPC password reset link.'],
    'reset_password.php' => ['Choose a New Password | GuideMyPC', 'Set a new GuideMyPC password with a valid reset link.'],
    'settings.php' => ['Account Settings | GuideMyPC', 'Update your GuideMyPC account settings.'],
];
$pageTitle = $pageTitle ?? ($pageDetails[$pageKey][0] ?? 'GuideMyPC | Practical Tech Support');
$pageDescription = $pageDescription ?? ($pageDetails[$pageKey][1] ?? 'Practical, trustworthy technology support from GuideMyPC.');
$canonicalPath = $canonicalPath ?? $pageKey;
$canonicalUrl = application_url($canonicalPath);
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="description" content="<?php echo e($pageDescription); ?>">
<link rel="canonical" href="<?php echo e($canonicalUrl); ?>">
<meta property="og:type" content="website">
<meta property="og:title" content="<?php echo e($pageTitle); ?>">
<meta property="og:description" content="<?php echo e($pageDescription); ?>">
<meta property="og:url" content="<?php echo e($canonicalUrl); ?>">
<meta name="twitter:card" content="summary">

<script>try{var theme=localStorage.getItem('guidemypc-theme');if(theme==='light'||theme==='dark'){document.documentElement.dataset.theme=theme;}}catch(error){}</script>

<title><?php echo e($pageTitle); ?></title>

<link rel="stylesheet" href="<?php echo e(asset_url('css/style.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset_url('css/design-system.css')); ?>">

</head>

<body>
<?php render_flash_messages(); ?>
