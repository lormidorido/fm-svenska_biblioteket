<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Fel</title>
    <link rel="stylesheet" href="aurum.css">
</head>
<body>
    <div id="header">
        <div id="headerlogo">
            svenska_biblioteket
            <div id="headercaption"></div>
        </div>
    </div>
    <div id="content">
        <?php include_once 'navigation.php'; ?>
        <table class="contentbg">
            <tr>
                <td class="contentbgleft"></td>
                <td class="contentmid">
                    <div id="contenttitlebg">
                        <h1>Fel</h1>
                    </div>
                    <div id="contenttext">
                        <p class="error">
                            <?php
                            $message = $_GET['message'] ?? 'Ett okänt fel inträffade.';
                            echo htmlspecialchars($message);
                            ?>
                        </p>
                    </div>
                </td>
                <td class="contentbgright"></td>
            </tr>
        </table>
    </div>
</body>
</html>