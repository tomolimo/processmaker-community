<?php

$filter = new InputFilter();
$_GET = $filter->xssFilterHard($_GET, "url");
switch ($RBAC->userCanAccess('PM_SUPERVISOR')) {
    case - 2:
        G::SendTemporalMessage('ID_USER_HAVENT_RIGHTS_SYSTEM', 'error', 'labels');
        G::header('location: ../login/login');
        die();
        break;
    case - 1:
        G::SendTemporalMessage('ID_USER_HAVENT_RIGHTS_PAGE', 'error', 'labels');
        G::header('location: ../login/login');
        die();
        break;
}
$_SESSION = $filter->xssFilterHard($_SESSION, "url");
if ((int) $_SESSION['INDEX'] < 1) {
    $_SERVER['HTTP_REFERER'] = $filter->xssFilterHard($_SERVER['HTTP_REFERER']);
    G::SendTemporalMessage('ID_USER_HAVENT_RIGHTS_PAGE', 'error', 'labels');
    G::header('location: ' . $_SERVER['HTTP_REFERER']);
    die();
}

/* GET , POST & $_SESSION Vars */
//$_SESSION['STEP_POSITION'] = (int)$_GET['POSITION'];


/* Menues */
$G_MAIN_MENU = 'processmaker';
$G_SUB_MENU = 'cases';
$G_ID_MENU_SELECTED = 'CASES';
$G_ID_SUB_MENU_SELECTED = 'CASES_TO_REVISE';

/* Prepare page before to show */
$oTemplatePower = new TemplatePower(PATH_TPL . 'cases/cases_Step.html');
$oTemplatePower->prepare();
$G_PUBLISH = new Publisher();
$oHeadPublisher = headPublisher::getSingleton();
//  Check if these code needs to be removed since the interface ar now moving to ExtJS
$oHeadPublisher->addScriptCode('
    var Cse = {};
    Cse.panels = {};
    var leimnud = new maborak();
    leimnud.make();
    leimnud.Package.Load("rpc,drag,drop,panel,app,validator,fx,dom,abbr",{Instance:leimnud,Type:"module"});
    leimnud.Package.Load("cases",{Type:"file",Absolute:true,Path:"/jscore/cases/core/cases.js"});
    leimnud.Package.Load("cases_Step",{Type:"file",Absolute:true,Path:"/jscore/cases/core/cases_Step.js"});
    leimnud.Package.Load("processmap",{Type:"file",Absolute:true,Path:"/jscore/processmap/core/processmap.js"});
    leimnud.exec(leimnud.fix.memoryLeak);
    leimnud.event.add(window,"load",function(){
        ' . (isset($_SESSION['showCasesWindow']) ? 'try{' . $_SESSION['showCasesWindow'] . '}catch(e){}' : '') . '
});
  ');
//  Check if these code needs to be removed since the interface ar now moving to ExtJS
$G_PUBLISH->AddContent('template', '', '', '', $oTemplatePower);

if (! isset($_GET['position'])) {
    $_GET['position'] = 1;
}

$_SESSION['STEP_POSITION'] = (int) $_GET['position'];
$oCase = new Cases();
$Fields = $oCase->loadCase($_SESSION['APPLICATION']);

$G_PUBLISH = new Publisher();
$ex = 0;
if (! isset($_GET['ex']) || empty($_GET['ex'])) {
    $_GET['ex'] = 0;
} else {
    $ex = $filter->xssFilterHard($_GET['ex']);
}

if (! isset($_GET['INP_DOC_UID'])) {
    $oCase = new Cases();
    $G_PUBLISH->AddContent('propeltable', 'paged-table', 'cases/cases_InputdocsListToRevise', $oCase->getInputDocumentsCriteriaToRevise($_SESSION['APPLICATION']), '');
} else {
    $oInputDocument = new InputDocument();
    $Fields = $oInputDocument->load($_GET['INP_DOC_UID']);
    switch ($Fields['INP_DOC_FORM_NEEDED']) {
        case 'REAL':
            $Fields['TYPE_LABEL'] = G::LoadTranslation('ID_NEW');
            $sXmlForm = 'cases/cases_AttachInputDocument2';
            break;
        case 'VIRTUAL':
            $Fields['TYPE_LABEL'] = G::LoadTranslation('ID_ATTACH');
            $sXmlForm = 'cases/cases_AttachInputDocument1';
            break;
        case 'VREAL':
            $Fields['TYPE_LABEL'] = G::LoadTranslation('ID_ATTACH');
            $sXmlForm = 'cases/cases_AttachInputDocument3';
            break;
    }
    $Fields['MESSAGE1'] = G::LoadTranslation('ID_PLEASE_ENTER_COMMENTS');
    $Fields['MESSAGE2'] = G::LoadTranslation('ID_PLEASE_SELECT_FILE');
    $docName = $Fields['INP_DOC_TITLE'];
    $oHeadPublisher->addScriptCode('var documentName=\'Reviewing Input Document<br>' . $docName . '\';');
    $G_PUBLISH->AddContent('view','cases/paged-table-inputDocumentsToReviseNavBar');
    $G_PUBLISH->AddContent('propeltable', 'cases/paged-table-inputDocumentsToRevise', 'cases/cases_ToReviseInputdocsList', $oCase->getInputDocumentsCriteria($_SESSION['APPLICATION'], $_SESSION['INDEX'], $_GET['INP_DOC_UID']), array_merge(['DOC_UID' => $_GET['INP_DOC_UID']], $Fields));
}

G::RenderPage('publish', 'blank');

?>

<script>
/*------------------------------ To Revise Routines ---------------------------*/
//Deprecated Section since the interface are now movig to ExtJS
function setSelect()
{
    var ex=<?php echo $ex; ?>;
    try {
        for (i=1; i<50; i++) {
            if (i == ex) {
                document.getElementById('focus'+i).innerHTML = '<img src="/images/bulletButton.gif" />';
            } else {
                document.getElementById('focus'+i).innerHTML = '';
            }
        }
    } catch (e){
        return 0;
    }
}
</script>

<?php
