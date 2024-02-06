<script>
if (typeof window.parent != 'undefined') {
<?php
if (isset($_GET['ux'])) {
    switch ($_GET['ux']) {
        case 'SIMPLIFIED':
        case 'SWITCHABLE':
        case 'SINGLE':
            $url = '../home';
            break;
        default:
            $url = 'casesListExtJs';
    }
} else {
    $url = 'casesListExtJs';
}
if (isset($_GET['ux'])) {
    echo 'if (typeof window.parent.ux_env != \'undefined\') {';
}
echo '  parent.parent.postMessage("redirect=todo","*");';
if (isset($_GET['ux'])) {
            echo '} else { parent.parent.postMessage("redirect=todo","*"); }';
}
echo "try {parent.parent.updateCasesTree();parent.parent.highlightCasesTree();} catch(e) {}";
?>
}
</script>