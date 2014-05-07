<?php
class HTMLElement {

	private static $aSelfClose = array('br','hr');

	public static function get($type,$attr=array(),$html=''){
		$aTypes = explode('>',$type);
		$type = array_shift($aTypes);
		if (count($aTypes)!==0) $html = self::get(implode('>',$aTypes),null,$html);
		$aAttrs = explode('@',$type);
		if (count($aAttrs)>1) {
			$type = array_shift($aAttrs);
			foreach ($aAttrs as $sAttr) {
				$aAttr = explode('=',$sAttr);
				if (count($aAttr)>1) $attr[$aAttr[0]] = $aAttr[1];
				else $attr[] = $aAttr[0];
			}
		}

		return !empty($html)||!in_array($type,HTMLElement::$aSelfClose)?'<'.$type.self::getAttrString($attr).'>'.$html.'</'.$type.'>':'<'.$type.self::getAttrString($attr).' />';
		//return $html!==null?'<'.$type.self::getAttrString($attr).'>'.$html.'</'.$type.'>':'<'.$type.self::getAttrString($attr).' />';
	}

	private static function getAttrString($attr=array()) {
		$bAttr = count($attr)>0;
		$sAttr = $bAttr?' ':'';
		if ($bAttr) {
			foreach ($attr as $k=>$v) {
				$sAttr .= is_numeric($k)?$v.' ':$k.'="'.$v.'" ';
			}
		}
		return $sAttr;
	}
}
