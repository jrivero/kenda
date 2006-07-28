<?php
#  openform(ACTION, METHOD);
#  closeform();
#  textinput(LABEL, NAME, DEFAULT VALUE, SIZE, MAXSIZE);
#  checkbox(LABEL, NAME, VALUE, CHECKED); <- poner CHECKED para marcar
#  textarea(LABEL, NAME, ROWS, COLUMNS);
#  combo(LABEL, NAME, SIZE, 'OPCION1,OPCION2,OPCIONx...');
#  radio(LABEL, NAME, VALUE, CHECKED); <- poner CHECKED para marcar
#  hidden(NAME, VALUE);
#  resetform(VALUE);
#  submit(VALUE);

class html_form {
	function openform($action,$method='post') { $frm = '<form action="'.$action.'" method="'.$method.'">'.NL; return $frm; }
	function closeform() { $frm = '</form>'.NL; return $frm; }

	function openfieldset($title = 'Formulario') {
	 $frm = '<fieldset>'.NL;
	 $frm.= ' <legend>'.$title.'</legend>'.NL;
	 return $frm;
	}
	function closefieldset() {
	 $frm = '</fieldset>'.NL;
	 return $frm;
	}

	function textinput($label, $name, $value = '', $size = '40', $maxlength = '255') {
	 $frm = NL;
	 $frm.= ' <div>'.NL;
	 $frm.= '  <label for="'.$name.'">'.$label.': </label>'.NL;
	 $frm.= '  <input type="text" id="'.$name.'" name="'.$name.'" size="'.$size.'" value="'.$value.'" maxlength="'.$maxlength.'" />'.NL;
	 $frm.= ' </div>'.NL.NL;
	 $frm.= ' <br />'.NL;
	 return $frm;
	}
	function checkbox($label, $name, $value, $checked = '') {
	        $frm.= "<label for=\"$name\">$label: </label>\n";
	        $frm.= "<input type=\"checkbox\" class=\"check\" id=\"$name\" name=\"$name\" value=\"$value\" $checked />\n";
					return $frm;
	        }
	function radio($label, $name, $value, $checked = '') {
	        $frm.= "<input type=\"radio\" id=\"$name\" name=\"$name\" value=\"$value\" $checked />\n";
	        $frm.= "<label for=\"$name\">$label</label>\n";
					return $frm;
	        }
	function textarea($label, $name, $rows = '2', $columns = '50') {
	        $frm.= "<label for=\"$name\">$label: </label>\n";
	        $frm.= "<textarea id=\"$name\" name=\"$name\" rows=\"$rows\" cols=\"$columns\"></textarea>\n";
					return $frm;
	        }
	function combo($label, $name, $size, $options) {
	 $frm = NL;
	 $frm.= ' <div>'.NL;
	 $frm.= '  <label for="'.$name.'">'.$label.': </label>'.NL;
	 $frm.= '  <select name="'.$name.'" size="'.$size.'">'.NL;
	 $opts = split (",", $options);
	 while (list($username, $subarray) = each($opts)) {
	  list($name, $value) = split ("-", $subarray);
	  $frm.= '   <option value="'.$value.'">'.$name.'</option>'.NL;
	 }
	 $frm.= '  </select>'.NL;
	 $frm.= ' </div>'.NL.NL;
	 $frm.= ' <br />'.NL;
	 return $frm;
	}
	function hidden($name, $value) { $frm = "<input type=\"hidden\" name=\"$name\" value=\"$value\">\n"; return $frm; }
	function submit($value) { $frm = '<input type="submit" class="frmButton" name="submit" value="'.$value.'" />'.NL; return $frm; }
	function resetform($value) { $frm = "<input type=\"reset\" value=\"$value\">\n"; }
}
?>