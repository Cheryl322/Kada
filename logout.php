<?php
// Start the session
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Clear the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Send no-cache headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Past date

// Redirect to login page with no-cache JavaScript
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logging Out...</title>
    <script>
        // Prevent going back to cached pages
        window.onload = function() {
            if(typeof window.history.pushState == 'function') {
                window.history.pushState({}, "Hide", '<?php echo $_SERVER['PHP_SELF'];?>');
            }
        }
        
        // Disable back button
        window.location.hash="no-back-button";
        window.location.hash="Again-No-back-button"; // Chrome
        window.onhashchange=function(){window.location.hash="no-back-button";}
        
        // Redirect to login page
        window.location.replace("login.php");
    </script>
</head>
<body>
    <p>Logging out...</p>
</body>
</html>