<?php
// navigation.php – moderniserad med aktiv länk

$links = [
    'Hem' => 'home.php',
    'Sök' => 'findrecords.php',
    'Lista över poster' => 'recordlist.php',
    'Visa alla' => 'recordlist.php?-action=findall'
];

$current = $_GET['-link'] ?? '';
?>

<div id="page_nav">
    <ul>
        <?php foreach ($links as $label => $url): ?>
            <li<?php if ($label === $current) echo ' class="activelink"'; ?>>
                <a href="<?= htmlspecialchars($url) ?>?-link=<?= urlencode($label) ?>">
                    <b><?= htmlspecialchars($label) ?></b>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>