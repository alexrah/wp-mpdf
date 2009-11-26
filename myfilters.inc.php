<?php
	/*
	 * This file is part of wp-mpdf.
	 * wp-mpdf is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free 		 * Software Foundation, either version 3 of the License, or (at your option) any later version.
	 *
	 * wp-mpdf is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 	 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License along with wp-mpdf. If not, see <http://www.gnu.org/licenses/>.
	 */


	require_once('./wp-content/plugins/wp-mpdf/get_mark.inc.php');
	
	function mpdf_myfilters($content) {
		$content = mpdf_clearcaption($content);
		$content = mpdf_buildmenu($content);
		$content = mpdf_prefix($content);
		$content = mpdf_prefix_clear($content);
		$content = mpdf_speedUpLocaleImages($content);
		
		return $content;
	}

	function mpdf_speedUpLocaleImages($content) {
		$base_url = get_option('siteurl');

		if(preg_match_all('#<img.*src="(.*)".*>#iU', $content, $matches)) {
			foreach($matches[1] as $ikey => $img) {
				if(strpos($img, $base_url) === 0 ) {
					$local_img_path = str_replace($base_url, '', $img);
					$new_img = ABSPATH . (substr($local_img_path, 0, 1) === '/' ? substr($local_img_path, 1) : $local_img_path);
					$content = str_replace($img, $new_img, $content);
				}
				else {
					if(substr($img, 0, 1) === '/') {
						$new_img = ABSPATH . $img;
						$content = str_replace($img, $new_img, $content);
					}
				}
			}
		}
		if(preg_match_all("#<img.*src='(.*)'.*>#iU", $content, $matches)) {
			foreach($matches[1] as $ikey => $img) {
				if(strpos($img, $base_url) === 0 ) {
					$local_img_path = str_replace($base_url, '', $img);
					$new_img = ABSPATH . (substr($local_img_path, 0, 1) === '/' ? substr($local_img_path, 1) : $local_img_path);
					$content = str_replace($img, $new_img, $content);
				}
				else {
					if(substr($img, 0, 1) === '/') {
						$new_img = ABSPATH . $img;
						$content = str_replace($img, $new_img, $content);
					}
				}
			}
		}
	}

	function mpdf_prefix_clear($content) {
		$tmpPre = get_mark($content, '<pre*>');
		for($i=0;$i<count($tmpPre);$i++) {
			$content = str_replace('<pre'.$tmpPre[$i].'>', '', $content);
		}
		$content = str_replace('</pre>', '', $content);

		$tmpPre = get_mark($content, '<PRE*>');
		for($i=0;$i<count($tmpPre);$i++) {
			$content = str_replace('<PRE'.$tmpPre[$i].'>', '', $content);
		}
		$content = str_replace('</PRE>', '', $content);

		return $content;
	}

	function mpdf_prefix($content) {
		$tmpPre = get_mark($content, '<pre*>');
		for($i=0;$i<count($tmpPre);$i++) {
			$tmpPreBlock = get_mark($content, '<pre'.$tmpPre[$i].'>*</pre>');
			for($i2=0;$i2<count($tmpPreBlock);$i2++) {
				$content = str_replace('<pre'.$tmpPre[$i].'>'.$tmpPreBlock[$i2].'</pre>', '<div class="pre">'.str_replace("\n", "<br />\n", $tmpPreBlock[$i2]).'</div>', $content);
			}
		}

		$tmpPre = get_mark($content, '<PRE*>');
		for($i=0;$i<count($tmpPre);$i++) {
			$tmpPreBlock = get_mark($content, '<PRE'.$tmpPre[$i].'>*</PRE>');
			for($i2=0;$i2<count($tmpPreBlock);$i2++) {
				$content = str_replace('<PRE'.$tmpPre[$i].'>'.$tmpPreBlock[$i2].'</PRE>', '<div class="PRE">'.str_replace("\n", "<br />\n", $tmpPreBlock[$i2]).'</div>', $content);
			}
		}

		return $content;
	}

	function mpdf_clearcaption($content) {
		$tmpBlock = get_mark($content, '[caption *]');
		for($i=0;$i<count($tmpBlock);$i++) {
			$content = str_replace('[caption '.$tmpBlock[$i].']', '', $content);
		}
		$content = str_replace('[/caption]', '', $content);
		
		return $content;
	}
	
	function mpdf_buildmenu($content) {
		$tmpBlock = get_mark($content, '<!--pagetitle:*-->');
		for($i=0;$i<count($tmpBlock);$i++) {
			$content = str_replace('<!--pagetitle:'.$tmpBlock[$i].'-->', '<h1>'.$tmpBlock[$i].'</h1><bookmark content="'.htmlspecialchars($tmpBlock[$i], ENT_QUOTES).'" level="2" /><tocentry content="'.htmlspecialchars($tmpBlock[$i], ENT_QUOTES).'" level="2" />', $content);
		}
		
		//den More filter
		$tmpFields = explode('<!--more-->', $content);
		$tmpContent = $tmpFields[0];
		if(count($tmpFields)>1) $tmpContent = $tmpFields[1];
		
		$nextLevel = 2;
		if(count($tmpBlock)>0) $nextLevel = 3;
		
		$tmpBlock = get_mark($tmpContent, '<strong>*</strong><br />');
		for($i=0;$i<count($tmpBlock);$i++) {
			$content = str_replace('<strong>'.$tmpBlock[$i].'</strong><br />', '<strong>'.$tmpBlock[$i].'</strong><bookmark content="'.htmlspecialchars(str_replace(array('<br />', ':'), '', $tmpBlock[$i]), ENT_QUOTES).'" level="'.$nextLevel.'" /><tocentry content="'.htmlspecialchars(str_replace(array('<br />', ':'), '', $tmpBlock[$i]), ENT_QUOTES).'" level="'.$nextLevel.'" /><br />', $content);
		}
		
		$tmpBlock = get_mark($tmpContent, '<strong>*</strong>'."\n");
		for($i=0;$i<count($tmpBlock);$i++) {
			$content = str_replace('<strong>'.$tmpBlock[$i].'</strong>', '<strong>'.$tmpBlock[$i].'</strong><bookmark content="'.htmlspecialchars(str_replace(array('<br />', ':'), '', $tmpBlock[$i]), ENT_QUOTES).'" level="'.$nextLevel.'" /><tocentry content="'.htmlspecialchars(str_replace(array('<br />', ':'), '', $tmpBlock[$i]), ENT_QUOTES).'" level="'.$nextLevel.'" />', $content);
		}
		
		if(count($tmpFields)>1) {
			$tmpBlock = get_mark($tmpFields[0], '<strong>*</strong><br />');
			for($i=0;$i<count($tmpBlock);$i++) {
				$content = str_replace('<strong>'.$tmpBlock[$i].'</strong><br />', '<strong>'.$tmpBlock[$i].'</strong><bookmark content="'.htmlspecialchars(str_replace(array('<br />', ':'), '', $tmpBlock[$i]), ENT_QUOTES).'" level="2" /><tocentry content="'.htmlspecialchars(str_replace(array('<br />', ':'), '', $tmpBlock[$i]), ENT_QUOTES).'" level="2" /><br />', $content);
			}
			
			$tmpBlock = get_mark($tmpFields[0], '<strong>*</strong>'."\n");
			for($i=0;$i<count($tmpBlock);$i++) {
				$content = str_replace('<strong>'.$tmpBlock[$i].'</strong>', '<strong>'.$tmpBlock[$i].'</strong><bookmark content="'.htmlspecialchars(str_replace(array('<br />', ':'), '', $tmpBlock[$i]), ENT_QUOTES).'" level="2" /><tocentry content="'.htmlspecialchars(str_replace(array('<br />', ':'), '', $tmpBlock[$i]), ENT_QUOTES).'" level="2" />', $content);
			}
		}
		
		
		$content = str_replace('<p><!--more--></p>', '', $content);
		
		return $content;
	}
?>
