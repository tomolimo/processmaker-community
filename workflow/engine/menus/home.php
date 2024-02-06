<?php

global $RBAC;
global $G_TMP_MENU;

// Home section
$G_TMP_MENU->AddIdRawOption('SEARCHS', '', G::LoadTranslation('ID_HOME'), '', '', 'blockHeader');
$G_TMP_MENU->AddIdRawOption(
    'CASES_MY_CASES',
    '/',
    G::LoadTranslation('ID_MY_CASES'),
    'fas fa-sliders-h'
);
if ($RBAC->userCanAccess('PM_ALLCASES') == 1) {
    $G_TMP_MENU->AddIdRawOption(
        'CASES_SEARCH',
        'casesListExtJs?action=search',
        G::LoadTranslation('ID_ADVANCEDSEARCH'),
        'fas fa-search'
    );
}


// Tasks section
$G_TMP_MENU->AddIdRawOption('FOLDERS', '', G::LoadTranslation('ID_TASKS'), '', '', 'blockHeader');
$G_TMP_MENU->AddIdRawOption(
    'CASES_INBOX',
    'casesListExtJs?action=todo',
    G::LoadTranslation('ID_INBOX'),
    'far fa-check-circle'
);
$G_TMP_MENU->AddIdRawOption(
    'CASES_DRAFT',
    'casesListExtJs?action=draft',
    G::LoadTranslation('ID_DRAFT'),
    'far fa-edit'
);
$G_TMP_MENU->AddIdRawOption(
    'CASES_PAUSED',
    'casesListExtJs?action=paused',
    G::LoadTranslation('ID_PAUSED'),
    'far fa-pause-circle'
);
$G_TMP_MENU->AddIdRawOption(
    'CASES_SELFSERVICE',
    'casesListExtJs?action=selfservice',
    G::LoadTranslation('ID_UNASSIGNED'),
    'fas fa-users'
);


// Supervisor Tasks section
$G_TMP_MENU->AddIdRawOption('ADMIN', '', G::LoadTranslation('ID_SUPERVISOR_TASKS'), '', '', 'blockHeader');
if ($RBAC->userCanAccess('PM_REASSIGNCASE') == 1 || $RBAC->userCanAccess('PM_REASSIGNCASE_SUPERVISOR') == 1) {
    $G_TMP_MENU->AddIdRawOption(
        'CASES_TO_REASSIGN',
        'casesListExtJs?action=to_reassign',
        G::LoadTranslation('ID_TASK_REASSIGNMENTS'),
        'fas fa-arrows-alt'
    );
}


// Documents section
if ($RBAC->userCanAccess('PM_FOLDERS_ALL') == 1 || $RBAC->userCanAccess('PM_FOLDERS_OWNER') == 1) {
    $G_TMP_MENU->AddIdRawOption('DOCUMENTS', '', G::LoadTranslation('ID_FOLDERS'), '', '', 'blockHeader');
    $G_TMP_MENU->AddIdRawOption(
        'CASES_FOLDERS',
        'casesStartPage?action=documents',
        G::LoadTranslation('ID_MY_DOCUMENTS'),
        'fas fa-bars'
    );
}
