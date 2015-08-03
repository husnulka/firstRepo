<?php
/*
 * Date : Wed, Feb 25, 2015
 */
//nanti kesini : /var/vsi-montools/payment/data-recon-v2
$sDBHost = "192.0.0.1";
$sDBUser = "root";
$sDBPwd = "p@ssword";
$sDBName = "";

function ConnectToDB(&$DBLink, &$DBConn, $sDBHost, $sDBUser, $sDBPwd, $sDBName, $newLink = false) {
    $DBLink = mysql_connect($sDBHost, $sDBUser, $sDBPwd, $newLink);
    if ($DBLink) {
        $DBConn = mysql_select_db($sDBName, $DBLink);
        if (!$DBConn) {
            echo $sErrMsg = mysql_error();
        }
    } else {
        echo $sErrMsg = mysql_error();
    }
}

// end of SCANPayment_ConnectToDB

function CloseDB(&$DBLink) {
    mysql_close($DBLink);
}

// end of SCANPayment_CloseDB

function getData($LinkDB, $tbl, $fldSelect = "*", $crt = "", $ord = "", $grp = "") {
    $qs = "SELECT " . $fldSelect . " FROM " . $tbl;
    $qs .= ($crt != '') ? " WHERE " . $crt . " " : "";
    $qs .= ($ord != '') ? " ORDER BY " . $ord : "";
    $qs .= ($grp != '') ? " GROUP BY " . $grp : "";
//echo $qs;
    $result = mysql_query($qs, $LinkDB);

    $rData = array();
    if ($result) {
        /* fetch associative array */
        while ($row = mysql_fetch_assoc($result)) {
            $rData[] = $row;
        }
    } else {
        die("Query [getAllData] Error!" . mysql_error());
    }//nelse
    mysql_free_result($result);
    return $rData;
}

function addData($LinkDB, $tbl, $value) {
    $bOk = false;
    $qs = "INSERT INTO " . $tbl . " SET " . $value;
//echo $qs;
    $result = mysql_query($qs, $LinkDB);

    $rData = array();
    if ($result) {
        if (mysql_affected_rows() > 0) {
            $bOk = true;
        }
    }

    return $bOk;
}

function changeData($LinkDB, $tbl, $val, $crt) {
    $bOk = false;

    $qs = "UPDATE " . $tbl . " SET " . $val;
    $qs.= ($crt != "") ? " WHERE " . $crt : "";

    $result = mysql_query($qs, $LinkDB);
    if ($result) {
        $bOk = true;
    }

    return $bOk;
}

function deleteData($LinkDB, $tbl, $crt) {
    $bOk = false;

    if ($crt != '') {
        $qs = "DELETE FROM " . $tbl;
        $qs .= " WHERE " . $crt;

        $result = mysql_query($qs, $LinkDB);
        if ($result) {
            if (mysql_affected_rows() > 0) {
                $bOk = true;
            }
        }
    }
    return $bOk;
}

/*
 * -------------------------------------------------------------
 * create connection
 * -------------------------------------------------------------
 */
$DBLink = $DBConn = NULL;
ConnectToDB($DBLink, $DBConn, $sDBHost, $sDBUser, $sDBPwd, $sDBName, $newLink = false);

/*
 * -------------------------------------------------------------
 * variable inisialisasi
 * -------------------------------------------------------------
 */
$chP = "checked";
$chC = "";

/*
 * -------------------------------------------------------------
 * action add/edit/delete
 * -------------------------------------------------------------
 */
$tbl = "CSCCORE_DOWN_CENTRAL_GROUP";
$recId = $recNm = $recPrnt = "";
$valHdnFrm = '<input type="hidden" name="ask" value="' . base64_encode('ad') . '">';
if (isset($_REQUEST['ask'])) {
    $ask = base64_decode(trim($_REQUEST['ask']));
    $psId = isset($_POST['txtId']) ? trim($_POST['txtId']) : "";
    $psNm = isset($_POST['txtNm']) ? trim($_POST['txtNm']) : "";
    if (isset($_POST['rdType']) && $_POST['rdType'] == '0') {
        $psPrnt = isset($psId) ? trim($psId) : "";
    } else {
        $psPrnt = isset($_POST['cmbPrnt']) ? trim($_POST['cmbPrnt']) : "";
    }
    switch ($ask) {
        case 'ad':
            $value = 'CSC_DC_ID="' . $psId . '", CSC_DC_NAME="' . $psNm . '", CSC_DC_PARENT="' . $psPrnt . '"';
            $bOk = addData($DBLink, $tbl, $value);

            if ($bOk) {
                echo '<script>'
                . 'alert("Berhasil menambah data ' . $psId . ' - ' . $psNm . '.");'
                . 'location.href="index.php";'
                . '</script>';
            } else {
                echo '<script>'
                . 'alert("GAGAL menambahkan  data ' . $psId . ' - ' . $psNm . '.");'
                . 'location.href="index.php";'
                . '</script>';
            }
            break;

        case 'ed':

            $valHdnFrm = '<input type="hidden" name="act" value="' . base64_encode('1') . '">';
            $reqId = (isset($_REQUEST['id'])) ? base64_decode(trim($_REQUEST['id'])) : "";
            $aDtId = getData($DBLink, $tbl, "*", "CSC_DC_ID='" . $reqId . "'", $ord = "");

            if (isset($aDtId) && !empty($aDtId)) {
                foreach ($aDtId as $dtId) {
                    $recId = $dtId['CSC_DC_ID'];
                    $recNm = $dtId['CSC_DC_NAME'];
                    $recPrnt = $dtId['CSC_DC_PARENT'];
                }
            }
            $chP = ($recId == $recPrnt) ? 'checked' : '';
            $chC = ($recId != $recPrnt) ? 'checked' : '';

            if (isset($_POST['act']) && base64_decode(trim($_POST['act'])) == '1') {
                $val = 'CSC_DC_ID="' . $psId . '", CSC_DC_NAME="' . $psNm . '", CSC_DC_PARENT="' . $psPrnt . '"';
                $bOk = changeData($DBLink, $tbl, $val, $crt = 'CSC_DC_ID="' . $reqId . '"');
                if ($bOk) {
                    echo '<script>'
                    . 'alert("Berhasil mengubah data ' . $reqId . '.");'
                    . 'location.href="index.php";'
                    . '</script>';
                } else {
                    echo '<script>'
                    . 'alert("GAGAL mengubah  data ' . $reqId . '.");'
                    . 'location.href="index.php";'
                    . '</script>';
                }
            }
            break;

        case 'dl':
            $reqId = (isset($_REQUEST['id'])) ? base64_decode(trim($_REQUEST['id'])) : "";
            $bOk = deleteData($DBLink, $tbl, $crt = 'CSC_DC_ID="' . $reqId . '"');
            if ($bOk) {
                echo '<script>'
                . 'alert("Berhasil menghapus data ' . $reqId . '.");'
                . 'location.href="index.php";'
                . '</script>';
            } else {
                echo '<script>'
                . 'alert("GAGAL menghapus  data ' . $reqId . '.");'
                . 'location.href="index.php";'
                . '</script>';
            }
            break;
        default:
    }
}

/* -----------------------------------------------------------
 * query untuk menampilkan seluruh record
 * -----------------------------------------------------------
 */
$crtCari = '';
$reqTxtCari = '';
if (isset($_POST['txtCari']) && trim($_POST['txtCari']) != '') {
    $reqTxtCari = trim($_POST['txtCari']);
    $crtCari = 'CSC_DC_ID like "' . $reqTxtCari . '" OR CSC_DC_NAME like "' . $reqTxtCari . '" OR CSC_DC_PARENT like "' . $reqTxtCari . '"';
}
$aDt = getData($DBLink, $tbl, "*", $crtCari, $ord = "CSC_DC_ID");

/* -----------------------------------------------------------
 * dan digunakan untuk membuat combobox parent pada form add/edit
 * -----------------------------------------------------------
 */
$aDtCmbParent = getData($DBLink, $tbl, "*", "", $ord = "CSC_DC_ID");

/* -----------------------------------------------------------
 * Display form add/edit data
 * -----------------------------------------------------------
 */
if (isset($_REQUEST['frm']) && trim($_REQUEST['frm']) == base64_encode('added')) {
    $styTxtPrnt = ($chC != '') ? 'style="display:none;"' : '';
    $styCmbPrnt = ($chP != '') ? 'style="display:none;"' : '';
    ?>
    <fieldset style="width: 800px;background-color: #ccccff">
        <legend>Tambah Data</legend>
        <form action="" method="post">
            <?php echo $valHdnFrm ?>
            <table>
                <tr>
                    <td>Type</td>
                    <td>: 
                        <input type="radio" name="rdType" value="0" <?php echo $chP ?> onclick="return ch('P')" />Parent 
                        <input type="radio" name="rdType" value="1" <?php echo $chC ?> onclick="return ch('C')" />Child
                    </td>
                </tr>
                <tr>
                    <td>ID</td>
                    <td>: <input type="text" name="txtId" id="txtId" value="<?php echo $recId ?>" onkeyup="Parent()" /></td>
                </tr>
                <tr>
                    <td>Name</td>
                    <td>: <input type="text" name="txtNm" value="<?php echo $recNm ?>" /></td>
                </tr>
                <tr>
                    <td>Parent</td>
                    <td>: 
                        <input type="text" name="txtPrnt" id="txtParentId" value="<?php echo $recPrnt ?>" <?php echo $styTxtPrnt ?> disabled />
                        <?php
                        if (isset($aDtCmbParent) && !empty($aDtCmbParent)) {

                            $sel = "";
                            echo '<select name="cmbPrnt" id="cmbParentId" ' . $styCmbPrnt . ' >';
                            foreach ($aDtCmbParent as $dtPrnt) {
                                $sel = ($recPrnt == $dtPrnt['CSC_DC_ID']) ? 'selected="selected"' : "";
                                echo '<option value="' . $dtPrnt['CSC_DC_ID'] . '" ' . $sel . '>' . $dtPrnt['CSC_DC_ID'] . ' - ' . $dtPrnt['CSC_DC_NAME'] . '</option>';
                            }
                            echo '</select>';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input type="submit" name="btnAdd" value="<?php echo ($recId != '') ? 'Edit' : 'Add' ?>">
                        <input type="reset" name="btnRst" value="Cancel" onclick="window.location = 'index.php'">
                    </td>
                </tr>
            </table>
        </form>
    </fieldset>
    <script>
        function ch(item) {
            document.getElementById("txtParentId").style.display = "none";
            document.getElementById("cmbParentId").style.display = "none";
            if (item == 'P') {
                document.getElementById("txtParentId").style.display = "";
                document.getElementById("cmbParentId").style.display = "none";
                document.getElementById("txtParentId").value = document.getElementById("txtId").value;
            } else {
                document.getElementById("txtParentId").style.display = "none";
                document.getElementById("cmbParentId").style.display = "";
            }
        }
        function Parent() {
            var x = document.getElementById("txtId").value;
            document.getElementById("txtParentId").value = x;
        }
    </script>
    <?php
} else { //n form add data

    /* -----------------------------------------------------------
     * display form pencarian
     * -----------------------------------------------------------
     */
    ?>
    <fieldset style="width: 350px;background-color: #ccffcc">
        <legend>Cari Data</legend>
        <form action="" method="post">
            <table>
                <tr>
                    <td>Data yang dicari </td>
                    <td>: <input type="text" name="txtCari" value="<?php echo $reqTxtCari ?>"/></td>
                    <td><input type="submit" name="btnCari" value="Cari"></td>
                </tr>
            </table>
        </form>
    </fieldset>
    <?php
    /* -----------------------------------------------------------
     * Display all data
     * -----------------------------------------------------------
     */
    $rHead = array("No.", "ID", "Name", "Parent", "Action");
    ?>
    <br/>
    <a href="index.php?frm=<?php echo base64_encode('added'); ?>">Tambah Data</a>
    <table border="1">
        <tr>
            <?php
            if (isset($rHead) && !empty($rHead)) {
                foreach ($rHead as $hd) {
                    echo '<td>' . $hd . '</td>';
                }
            }
            ?>
        </tr>
        <?php
        if (isset($aDt) && !empty($aDt)) {
            $i = 0;
            foreach ($aDt as $dt) {
                $i ++;
                echo '<tr>'
                . '<td>' . $i . '</td>'
                . '<td>' . $dt['CSC_DC_ID'] . '</td>'
                . '<td>' . $dt ['CSC_DC_NAME'] . '</td>'
                . '<td>' . $dt ['CSC_DC_PARENT'] . '</td>'
                . '<td> '
                . '<a href="index.php?ask=' . base64_encode("ed") . '&id=' . base64_encode($dt['CSC_DC_ID']) . '&frm=' . base64_encode('added') . '">Edit</a> |'
                . '<a href="#" onclick="if(confirm(\'Hapus data ' . $dt['CSC_DC_ID'] . ' ? \'))window.location=\'index.php?ask=' . base64_encode("dl") . '&id=' . base64_encode($dt['CSC_DC_ID']) . '\'">Hapus</a>'
                . '</td>'
                . '</tr>';
            }
        }
        ?>
    </table>
    <?php
}//nelse ?>