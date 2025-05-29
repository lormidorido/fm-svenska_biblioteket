<?php
require_once 'functions.php';
require_once 'settings.php';

// Lista över sökbara fält
$searchableFields = [
    'författare', 'titel', 'serie', 'Årtal', 'Utgivningsort',
    'Sidor', 'ISBN', 'förlag', 'Placering'
];

// Kontroll om vi ska utföra en sökning (om inte back=1 finns)
$hasSearch = false;

if (empty($_GET['back'])) {
    foreach ($_GET as $key => $value) {
        if (
            in_array($key, $searchableFields) ||
            preg_match('/^op_/', $key) ||
            in_array($key, ['match', 'limit', 'sortfield', 'sortorder'])
        ) {
            if (!empty($value)) {
                $hasSearch = true;
                break;
            }
        }
    }
}

// Om sökning sker, omdirigera till recordlist.php med alla parametrar
if ($hasSearch) {
    $params = [];
    foreach ($searchableFields as $field) {
        $value = $_GET[$field] ?? '';
        $op = $_GET["op_$field"] ?? 'contains';
        if ($value !== '') {
            $params[$field] = $value;
            $params["op_$field"] = $op;
        }
    }

    $params['match'] = $_GET['match'] ?? 'all';
    $params['limit'] = $_GET['limit'] ?? 25;
    $params['sortfield'] = $_GET['sortfield'] ?? '';
    $params['sortorder'] = $_GET['sortorder'] ?? 'ascend';
    $params['-link'] = 'Lista över poster';

    $queryString = http_build_query($params);
    header("Location: recordlist.php?$queryString");
    exit;
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Sök poster</title>
    <link rel="stylesheet" href="css/aurum.css">
</head>
<body>
<a href="#content" class="skip-link">Hoppa till innehåll</a>

<?php include 'header.php'; ?>

<div id="wrapper">
    <div id="navigation">
        <?php $_GET['-link'] = 'Sök'; include 'navigation.php'; ?>
    </div>

    <div id="content">
        <form method="get" class="search-box" style="background-color:#fdf3e4; padding:20px; margin:20px; border-radius:5px;">
            <?php foreach ($searchableFields as $field): ?>
                <div class="form-row" style="margin-bottom: 10px;">
                    <label for="<?= $field ?>" style="display: inline-block; width: 120px;"><?= ucfirst($field) ?>:</label>

                    <select name="op_<?= $field ?>">
                        <?php foreach (['contains' => 'Innehåller', 'equals' => 'Är exakt', 'starts' => 'Börjar med', 'ends' => 'Slutar med', 'not' => 'Inte'] as $op => $label): ?>
                            <option value="<?= $op ?>" <?= ($_GET["op_$field"] ?? '') === $op ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input type="text" name="<?= $field ?>" id="<?= $field ?>" value="<?= htmlspecialchars($_GET[$field] ?? '') ?>">
                </div>
            <?php endforeach; ?>

            <div class="form-row">
                <label>Sökmetod:</label>
                <input type="radio" name="match" value="any" <?= ($_GET['match'] ?? '') === 'any' ? 'checked' : '' ?>> Något fält matchar
                <input type="radio" name="match" value="all" <?= ($_GET['match'] ?? 'all') === 'all' ? 'checked' : '' ?>> Alla fält måste matcha
            </div>

            <div class="form-row">
                <label for="limit">Visa:</label>
                <select name="limit" id="limit">
                    <?php foreach ([10, 25, 50, 100] as $val): ?>
                        <option value="<?= $val ?>" <?= ($_GET['limit'] ?? 25) == $val ? 'selected' : '' ?>>
                            <?= $val ?>
                        </option>
                    <?php endforeach; ?>
                </select> Poster
            </div>

            <div class="form-row">
                <label for="sortfield">Sortera efter:</label>
                <select name="sortfield" id="sortfield">
                    <option value="" <?= empty($_GET['sortfield']) ? 'selected' : '' ?>>(ingen sortering)</option>
                    <option value="titel" <?= ($_GET['sortfield'] ?? '') === 'titel' ? 'selected' : '' ?>>Titel</option>
                    <option value="författare" <?= ($_GET['sortfield'] ?? '') === 'författare' ? 'selected' : '' ?>>Författare</option>
                    <option value="Årtal" <?= ($_GET['sortfield'] ?? '') === 'Årtal' ? 'selected' : '' ?>>Årtal</option>
                </select>

                <select name="sortorder" id="sortorder">
                    <option value="ascend" <?= ($_GET['sortorder'] ?? '') === 'ascend' ? 'selected' : '' ?>>Stigande</option>
                    <option value="descend" <?= ($_GET['sortorder'] ?? '') === 'descend' ? 'selected' : '' ?>>Fallande</option>
                </select>
            </div>

            <div class="form-row">
                <button type="submit" class="nav-button">Sök efter poster</button>
                <button type="button" class="nav-button" onclick="window.location='findrecords.php';">Återställ formulär</button>
            </div>
        </form>
    </div>

<?php include 'footer.php'; ?>
</body>
</html>
