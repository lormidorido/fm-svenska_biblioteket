<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Hem</title>
    <link rel="stylesheet" href="css/aurum.css">
</head>
<body>
    <div id="wrapper">
        <div id="header">
            <div id="headerlogo">svenska_biblioteket</div>
            <div id="headercaption">Institutionen för svenska, flerspråkighet och språkteknologi</div>
        </div>
        <div id="navigation">
            <?php include 'navigation.php'; ?>
        </div>
        <div id="content">
            <div id="contentleft"></div>
            <p style="padding: 20px;">
                Detta är startsidan för bibliotekssystemet. Använd menyn ovan för att söka, visa eller navigera i poster.
            </p>
        </div>
        <div id="footer">
            <?php include 'footer.php'; ?>
        </div>
    </div>
</body>
</html>