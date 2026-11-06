<?php 
include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/headfoot.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF_Local extends MYPDF{
	//Page header
	public function Header() {
		$fn = Zend_Registry::get('fn');
		$cpCfg = Zend_Registry::get('cpCfg');

		//$images = '<img src="images/HMS Logo.png" width="62px" height="60px"/>';
		$images = '<img src="images/HMS Logo.png"  width="60px" height="55px"/>';

		/*
		$header='
		<table border="0" width="100%" style="border-bottom:2px solid #000000;">
            <tr>
                <td width="10%">'.$images.'</td>
                <td width="90%" align="center"><br/>
                    <span style="font-size:22px;"><b>RICH MAPS SDN BHD</b></span><br/>
                    <span>NO. 20, 1ST FLOOR, JALAN 34/154, TAMAN BUKIT ANGGERIK,
                      56000 CHERAS, KUALA LUMPUR, 
                    TEL: 03-9101 1153 FAX: 03-9102 6616</span>
                </td>
            </tr>
        </table>
		';
		*/
        $siteRec = $fn->getRecordRowByID('site', 'site_id', $fn->getSessionParam('cp_site_id'));

		$header='
		<table border="0" width="100%" style="border-bottom:2px solid #000000;">
            <tr>
                <td width="10%">'.$images.'</td>
                <td width="90%" align="center"><br/>
                    <span style="font-size:22px;"><b>RICH MAPS SDN BHD</b></span><br/>
                    <span>'.$siteRec['address1']. ' ' . $siteRec['address2'] . '<br/>' .$siteRec['address_state']. ' ' . $siteRec['address_town'].'<br/>'.
                    $siteRec['phone'].'</span>
                </td>
            </tr>
        </table>
		';
		/*		
		$header='
		<table border="0" width="100%" cellpadding="4">
            <tr>
                <td width="68%" style="line-height:20px;"><br/>
                    <span style="font-size:22px;"><b>DENTCARE</b></span><br/>
                    <span><b>Klinic Pakar Pergigian / Dental Specialist Clinic</b><br/></span>
                    <span>Connaught Avenue, Cheras, KL (03-91081153)</span><br/>
                    <span>Bukit Anggerik, Cheras, KL (03-91011153)</span><br/>
                    <span>Solaris Mont Kiara (03-62113153)</span><br/>
                    <span>Petaling Jaya Old Town (03-77824354)</span>
                </td>
                <td width="32%">
                	<table border="0" width="100%">
                		<tr>
	                		<td width="60%" align="left" style="line-height:110px;"><b>MC:</b></td>
	                		<td width="42%" align="right">'.$images.'</td>
	                	</tr>
	                	<tr>
	                		<td colspan="2" align="center" style="border-top:1px solid black;"><b>MEDICAL CERTIFICATE</b></td>
	                	</tr>
	                	<tr>
	                		<td colspan="2" align="center" style="border-bottom:1px solid black;">SURAT AKUAN DOKTOR</td>
	                	</tr>
	                </table>
	            </td>
            </tr>
        </table>
		';*/


		$this->writeHTML($header, true, false, false, false, '');
		$this->SetTopMargin(25);

	}

	public function Footer() {
		$cpCfg = Zend_Registry::get('cpCfg');

      	// Page number
      	//$this->Cell(0, 10, '(This is computer generated document, and does not require a signature) Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'R');

      	$footer='
      	<table border="0" width="100%">
	        <tr>
	            <td align="center">
	            </td>
	        </tr>
			<tr>
				<td width="78%">(This is computer generated document, and does not require a signature)</td>
				<td width="22%" align="right">Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages().'</td>
			</tr>
		</table>';
		//$this->writeHTML($footer, true, false, false, false, '');
    }
}
?>