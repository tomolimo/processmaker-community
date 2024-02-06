<?php

$navbar = PmDynaform::navigationBarForStepsToRevise($_GET['APP_UID'], $_GET['INP_DOC_UID'], $_GET['DEL_INDEX']);

echo '<div class="pagedTableDefault" style="border:none;margin:0px;padding:0px;margin-right:20px;margin-top:-3px;">'
 . '<div class="headerContent" style="border:none;margin:0px;padding:0px;">'
 . '<div class="tableOption" style="border:none;margin:0px;padding:0px;">'
 . $navbar
 . '</div>'
 . '</div>'
 . '</div>';
