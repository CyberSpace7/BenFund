<?php
/* 
   File Thingie version 2.0 - Andreas Haugstrup Pedersen <andreas@solitude.dk> December 20th, 2006
   The newest version of File Thingie can be found at <http://www.solitude.dk/filethingie/>
   Comments, suggestions etc. are welcome and encouraged at the above e-mail.
   
   LICENSE INFORMATION FOR FILE THINGIE:
   This work is licensed under the Creative Commons Attribution-NoDerivs-NonCommercial.
   To view a copy of this license, visit <http://creativecommons.org/licenses/by-nc-nd/2.5/dk/deed.en_GB>
   If you want to use File Thingie in a commercial setting please contact me at <andreas@solitude.dk>
   The cost for a commercial license of File Thingie is $20 (discounts available for bulk purchases).

   The jQuery javascript library have been included in File Thingie.
   The bundled version is 1.0.4. jQuery is covered by the following license:

   Copyright (c) 2006 John Resig, http://jquery.com/

   Permission is hereby granted, free of charge, to any person obtaining
   a copy of this software and associated documentation files (the
   "Software"), to deal in the Software without restriction, including
   without limitation the rights to use, copy, modify, merge, publish,
   distribute, sublicense, and/or sell copies of the Software, and to
   permit persons to whom the Software is furnished to do so, subject to
   the following conditions:

   The above copyright notice and this permission notice shall be
   included in all copies or substantial portions of the Software.

   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
   EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
   MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
   NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
   LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
   OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
   WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
   
*/
# Settings - Change as appropriate. See online documentation for explanations. #
define("USERNAME", "admin"); // Your default username.
define("PASSWORD", "pass"); // Your default password.
define("DIR", "/benfund.com"); // Your default directory. Do NOT include a trailing slash!

define("MAXSIZE", 1000000); // Maximum file upload size - in bytes.
define("DISABLELOGIN", FALSE); // Set to TRUE if you want to disable password protection.
define("FILEBLACKLIST", "file_manager.php"); // Specific files that will not be shown.
define("FILETYPEBLACKLIST", ""); // File types that are not allowed for upload.
define("FILETYPEWHITELIST", ""); // Add file types here to *only* allow those types to be uploaded.
define("EDITLIST", "txt html css php inc"); // List of file types that can be edited.
define("DISABLEUPLOAD", FALSE); // Set to TRUE if you want to disable file uploads.
define("DISABLEFILEACTIONS", FALSE); // Set to TRUE if you want to disable file actions (rename, move, delete, edit, duplicate).
define("CONVERTTABS", FALSE); // Set to TRUE to convert tabs to spaces when editing a file.
# Colours #
define("COLOURONE", "#326532"); // Dark background colour - also used on menu links.
define("COLOURONETEXT", "#fff"); // Text for the dark background.
define("COLOURTWO", "#DAE3DA"); // Brighter color (for table rows and sidebar background).
define("COLOURTEXT", "#000"); // Regular text colour.
define("COLOURHIGHLIGHT", "#ffc"); // Hightlight colour for status messages.
# Additional users #
/*
$users['REPLACE_WITH_USERNAME']['password'] = "REPLACE_WITH_PASSWORD";
$users['REPLACE_WITH_USERNAME']['dir'] = "REPLACE_WITH_CUSTOM_DIRECTORY";
*/
# Version #
define("VERSION", "2.0"); // Current version of File Thingie.

# Various helper functions #
function checkLogin() { // Checks whether a login is valid or not.
	global $users;
	if (DISABLELOGIN == FALSE) {
		if (empty($_SESSION['ft_user'])) {
			// Session variable has not been set. Check if login form has been submitted or return false.
			if ($_POST['act'] == "dologin") {
				// Check username and password from login form.
				if ($_POST['ft_user'] == USERNAME && $_POST['ft_pass'] == PASSWORD) {
					// Valid login. Set session variables and return true.
					$_SESSION['ft_user'] = USERNAME;
					redirect();
				}
				// Default user was not valid, we check additional users (if any).
				if (is_array($users) && sizeof($users) > 0) {
					// Check username and password.
					if (array_key_exists($_POST['ft_user'], $users) && $users[$_POST['ft_user']]['password'] == $_POST['ft_pass']) {
						// Valid login.
						$_SESSION['ft_user'] = $_POST['ft_user'];
						redirect();						
					}
				}
				redirect("act=error");
			}
			return FALSE;
		} else {
			return TRUE;
		}
	} else {
		return TRUE;
	}
}
function redirect($query = '') { // Redirects to that location.
	if (stristr($_SERVER["REQUEST_URI"], "?")) {
		$requesturi = substr($_SERVER["REQUEST_URI"], 0, strpos($_SERVER["REQUEST_URI"], "?"));
		$location = "Location: https://www.benfund.com/admin/ft2.php";
	} else {
		$requesturi = $_SERVER["REQUEST_URI"];
		$location = "Location: https://www.benfund.com/admin/ft2.php";
	}
	if (!empty($query)) {
		$location .= "?{$query}";
	}
	header($location);
	exit;
}
function sanitizeREQUEST() { // Goes through REQUEST variables used by file Thingie and removes attempts to hijack.
	// Make sure 'dir' cannot be changed to open directories outside the stated FT directory.
	if (strstr($_REQUEST['dir'], "..") || empty($_REQUEST['dir'])) {
		unset ($_REQUEST['dir']);
	}
	// Nuke slashes from 'file' and 'newvalue'
	$_REQUEST['file'] = str_replace("/", "", $_REQUEST['file']);
	if ($_REQUEST['act'] != "move") {
		$_REQUEST['newvalue'] = str_replace("/", "", $_REQUEST['newvalue']);
		// Nuke ../ for 'newvalue' when not moving files.
		if (stristr($_REQUEST['newvalue'], "..") || empty($_REQUEST['newvalue'])) {
			unset ($_REQUEST['newvalue']);
		}
	}
	// Nuke ../ for 'file'
	if (stristr($_REQUEST['file'], "..") || empty($_REQUEST['file'])) {
		unset ($_REQUEST['file']);
	}
}
function getExt ($name) { // Returns the file ending without the "."
	if (strstr($name, ".")) {
		$ext = str_replace(".", "", strrchr($name, "."));
	} else {
		$ext = "";
	}
	return $ext;
}
function niceFileSize($size) { // Converts a file size to a nicer kilobytes value
	if (strlen($size) > 6) { // Convert to megabyte
		return round($size/(1024*1024), 2)." MB";
	} elseif (strlen($size) > 4 || $size > 1024) { // Convert to kilobyte
		return round($size/1024, 0)." Kb";		
	} else {
		return $size." b";
	}
}
function getSelf() { // Returns the location of File Thingie. Used in form actions.
	return basename($_SERVER['PHP_SELF']);
}
function checkfile($file) {
	// Check against file blacklist.
	if (FILEBLACKLIST != "") {
		$blacklist = explode(" ", FILEBLACKLIST);
		if (in_array($file, $blacklist)) {
			return FALSE;
		} else {
			return TRUE;
		}
	} else {
		return TRUE;
	}
}
function checkforedit($file) {
	// Check against file blacklist.
	if (EDITLIST != "") {
		$list = explode(" ", EDITLIST);
		if (in_array(getExt($file), $list)) {
			return TRUE;
		} else {
			return FALSE;
		}
	} else {
		return FALSE;
	}
}
function checkfiletype($file) {
	$type = getExt($file);
	// Check if we are using a whitelist.
	if (FILETYPEWHITELIST != "") {
		// User wants a whitelist
		$whitelist = explode(" ", FILETYPEWHITELIST);
		if (in_array($type, $whitelist)) {
			return TRUE;
		} else {
			return FALSE;
		}		
	} else {
		// Check against file blacklist.
		if (FILETYPEBLACKLIST != "") {
			$blacklist = explode(" ", FILETYPEBLACKLIST);
			if (in_array($type, $blacklist)) {
				return FALSE;
			} else {
				return TRUE;
			}
		} else {
			return TRUE;
		}
	}
}
function getDir() { // Get the current working directory.
	global $users;
	if ($_SESSION['ft_user'] == USERNAME) {
		// Default user. Apply default directory.
		if (empty($_REQUEST['dir'])) {
			return DIR;
		} else {
			return DIR.$_REQUEST['dir'];
		}		
	} else {
		// Use user dir.
		if (isset($users[$_SESSION['ft_user']]['dir'])) {
			$temp = $users[$_SESSION['ft_user']]['dir'];
		} else {
			$temp = DIR;
		}
		if (empty($_REQUEST['dir'])) {
			return $temp;
		} else {
			return $temp.$_REQUEST['dir'];
		}		
	}
}
function getFileList($dir) { // Returns an array of files in a directory.
	$filelist = array();
	$subdirs = array();
	if ($dirlink = @opendir($dir)) {
		// Creates an array with all file names in current directory.
		while (($file = readdir($dirlink)) !== false) {
			if ($file != "." && $file != ".." && checkfile($file) && checkfiletype($file)) { // Hide these two special cases and files and filetypes in blacklists.
				$c = array();
				$c['name'] = $file;
				$c['type'] = "file";
				$c['writeable'] = is_writeable("{$dir}/{$file}");
				if (checkforedit($file)) {
					$c['edit'] = TRUE;
				}
				// $c['modified'] = filemtime("{$dir}/{$file}");
				$c['size'] = filesize("{$dir}/{$file}");
				if (is_dir("{$dir}/{$file}")) {
					$c['size'] = 0;
					$c['type'] = "dir";
					if ($sublink = @opendir("{$dir}/{$file}")) {
						while (($current = readdir($sublink)) !== false) {
							if ($current != "." && $current != ".." && checkfile($current)) {
								$c['size']++;
							}
						}
						closedir($sublink);
					}
					$subdirs[] = $c;
				} else {
					$filelist[] = $c;
				}
			}
		}
		closedir($dirlink);
		return array_merge($subdirs, $filelist);
	} else {
		return "dirfail";
	}
}
function doAction() { // This function handles all actions (upload, rename, delete, mkdir, save after edit, duplicate file, create new file, logout)
	# mkdir
	if ($_REQUEST['act'] == "mkdir" && DISABLEUPLOAD == FALSE) {
		// Create directory.
		// Check input.
		if (strstr($_POST['mkdir'], ".")) {
			// Throw error (redirect).
			redirect("dir={$_REQUEST['dir']}&status=mkdirfail");
		} else {
			$_POST['mkdir'] = stripslashes($_POST['mkdir']);
			$newdir = getDir()."/{$_POST['mkdir']}";
			$oldumask = umask(0);
			if (@mkdir($newdir, 0777)) {
				// Redirect.
				redirect("dir={$_REQUEST['dir']}&status=mkdir");
			} else {
				// Redirect
				redirect("dir={$_REQUEST['dir']}&status=mkdirfail");
			}
			umask($oldumask);
		}
	# Save edited file
	} elseif ($_REQUEST['act'] == "savefile" && DISABLEFILEACTIONS == FALSE) {
		// Save a file that has been edited.
		$file = trim(stripslashes($_REQUEST["file"]));
		// Check for edit or cancel
		if (strtolower($_REQUEST["submit"]) != "cancel") {
			// Check if file type can be edited.
			if (checkforedit($file)) {
				$filecontent = stripslashes($_REQUEST["filecontent"]);
				if ($_REQUEST["convertspaces"] != "") {
					$filecontent = str_replace("    ", "\t", $filecontent);
				}
				if (is_writeable(getDir()."/{$file}")) {
					$fp = @fopen(getDir()."/{$file}", "wb");
					if ($fp) {
						fputs ($fp, $filecontent);
						fclose($fp);
						redirect("dir={$_REQUEST['dir']}&status=edit&old={$file}");
					} else {
						redirect("dir={$_REQUEST['dir']}&status=editfilefail&old={$file}");
					}
				} else {
					redirect("dir={$_REQUEST['dir']}&status=editfilefail&old={$file}");
				}
			} else {
				redirect("dir={$_REQUEST['dir']}&status=edittypefail&old={$file}");
			}
		} else {
			redirect("dir={$_REQUEST['dir']}");
		}
	# Move
	} elseif ($_REQUEST['act'] == "move" && DISABLEFILEACTIONS == FALSE) {
		// Check that both file and newvalue are set.
		$file = trim(stripslashes($_REQUEST['file']));
		$dir = trim(stripslashes($_REQUEST['newvalue']));
		if (substr($dir, -1, 1) != "/") {
			$dir .= "/";
		}
		// Check for level.
		if (substr_count($dir, "../") <= substr_count(getDir(), "/")) {
			$dir  = getDir()."/".$dir;
			if (!empty($file) && file_exists(getDir()."/".$file)) {
				// Check that destination exists and is a directory.
				if (is_dir($dir)) {
					// Move file.
					if (@rename(getDir()."/".$file, $dir."/".$file)) {
						// Success.
						redirect("dir={$_REQUEST['dir']}&status=move&old={$file}&new={$dir}");
					} else {
						// Error rename failed.
						redirect("dir={$_REQUEST['dir']}&status=movefail&old={$file}");
					}
				} else {
					// Error dest. isn't a dir or doesn't exist.
					redirect("dir={$_REQUEST['dir']}&status=movedestfail&old={$dir}");
				}
			} else {
				// Error source file doesn't exist.
				redirect("dir={$_REQUEST['dir']}&status=movesourcefail&old={$file}");
			}
		} else {
			// Error level
			redirect("dir={$_REQUEST['dir']}&status=movelevelfail&old={$file}");			
		}
	# Delete
	} elseif ($_REQUEST['act'] == "delete" && DISABLEFILEACTIONS == FALSE) {
		// Check that file is set.
		$file = stripslashes($_REQUEST['file']);
		if (!empty($file) && checkfile($file)) {
			if (is_dir(getDir()."/".$file)) {
				if (!@rmdir(getDir()."/".$file)) {
					redirect("dir={$_REQUEST['dir']}&status=rmdirfail&old={$file}");
				} else {
					redirect("dir={$_REQUEST['dir']}&status=rmdir&old={$file}");
				}
			} else {
				if (!@unlink(getDir()."/".$file)) {
					redirect("dir={$_REQUEST['dir']}&status=rmfail&old={$file}");
				} else {
					redirect("dir={$_REQUEST['dir']}&status=rm&old={$file}");
				}
			}
		} else {
			redirect("dir={$_REQUEST['dir']}&status=rmfail&old={$file}");			
		}
	# Rename && Duplicate
	} elseif ($_REQUEST['act'] == "rename" || $_REQUEST['act'] == "duplicate" && DISABLEFILEACTIONS == FALSE) {
		// Check that both file and newvalue are set.
		$old = trim(stripslashes($_REQUEST['file']));
		$new = trim(stripslashes($_REQUEST['newvalue']));
		if (!empty($old) && !empty($new)) {
			if (checkfiletype($new)) {
				// Make sure destination file doesn't exist.
				if (!file_exists(getDir()."/".$new)) {
					// Check that file exists.
					if (is_writeable(getDir()."/".$old)) {
						if ($_REQUEST['act'] == "rename") {
							if (@rename(getDir()."/".$old, getDir()."/".$new)) {
								// Success.
								redirect("dir={$_REQUEST['dir']}&status=rename&old={$old}&new={$new}");
							} else {
								// Error rename failed.
								redirect("dir={$_REQUEST['dir']}&status=renamefail&old={$old}");						
							}						
						} else {
							if (@copy(getDir()."/".$old, getDir()."/".$new)) {
								// Success.
								redirect("dir={$_REQUEST['dir']}&status=duplicate&old={$old}&new={$new}");
							} else {
								// Error rename failed.
								redirect("dir={$_REQUEST['dir']}&status=duplicate&old={$old}");						
							}
						}
					} else {
						// Error old file isn't writeable.
						redirect("dir={$_REQUEST['dir']}&status={$_REQUEST['act']}writefail&old={$old}");
					}
				} else {
					// Error destination exists.
					redirect("dir={$_REQUEST['dir']}&status={$_REQUEST['act']}destfail&old={$new}");					
				}
			} else {
				// Error file type not allowed.
				redirect("dir={$_REQUEST['dir']}&status={$_REQUEST['act']}typefail&new={$new}");
			}
		} else {
			// Error. File name not set.
			redirect("dir={$_REQUEST['dir']}&status={$_REQUEST['act']}emptyfail");
		}
	# upload
	} elseif ($_REQUEST['act'] == "upload" && DISABLEUPLOAD == FALSE) {
		// If we are to upload a file we will do so.
		$oklist = array();
		$errorlist = array();
		$errortype = array();
		foreach ($_FILES as $k => $c) {
			if (!empty($c['name'])) {
				$c['name'] = stripslashes($c['name']);
				if ($c['error'] == 0) {
					// Upload was successfull
					if (checkfiletype($c['name'])) {
						if (file_exists(getDir()."/{$c['name']}")) {
							$errorlist[$k] = $c['name'];
							$errortype[$k] = 6;						
						} else {
							if (@move_uploaded_file($c['tmp_name'], getDir()."/{$c['name']}")) {
								@chmod(getDir()."/{$c['name']}");
								// Success!
								$oklist[$k] = $c['name'];
							} else {
								// File couldn't be moved. Throw error.
								$errorlist[$k] = $c['name'];
								$errortype[$k] = 0;
							}
						}
					} else {
						// File type is not allowed. Throw error.
						$errorlist[$k] = $c['name'];
						$errortype[$k] = 1;
					}
				} else {
					// An error occurred.
					$errorlist[$k] = $c['name'];
					switch($_FILES["localfile"]["error"]) {
						case 1:
							$errortype[$k] = 2;
							break;
						case 2:
							$errortype[$k] = 2;
							break;
						case 3:
							$errortype[$k] = 3;
							break;
						case 4:
							$errortype[$k] = 4;
							break;
						default:
							$errortype[$k] = 5;
							break;
					}
				}
			}
		}
		if (count($oklist) > 0) {
			$oklist = "&oklist=".join(";", $oklist);
		} else {
			$oklist = "";
		}
		if (count($errorlist) > 0) {
			$errorlist = "&errorlist=".join(";", $errorlist)."&errortype=".join(";", $errortype);
		} else {
			$errorlist = "";
		}
		if (strlen($oklist) > 0 || strlen($errorlist) > 0) {
			redirect("dir={$_REQUEST['dir']}&status=upload{$oklist}{$errorlist}");			
		} else {
			redirect("dir={$_REQUEST['dir']}&status=uploadfail");
		}
	# logout
	} elseif ($_REQUEST['act'] == "logout") {
		$_SESSION = array();
		if (isset($_COOKIE[session_name()])) {
		   setcookie(session_name(), '', time()-42000, '/');
		}
		session_destroy();
		redirect();
	}
}

# makeXX function all return HTML to be displayed in the browser. #
function makeLink($text, $query = "", $title = "") { // Makes an HTML link - used for internal links
	$str = "<a href=\"".getSelf();
	if (!empty($query)) {
		$str .= "?{$query}";
	}
	$str .= "\"";
	if (!empty($title)) {
		$str .= "title=\"{$title}\"";
	}	
	$str .= ">{$text}</a>";
	return $str;
}
function makeLogin() { // Make a login box.
	$str = "<h1>File Thingie Login</h1>";
	$str .= '<form action="'.getSelf().'" method="post" id="loginbox">';
	if ($_REQUEST['act'] == "error") {
		$str .= "<p class='error'>Invalid username or password</p>";
	}
	$str .= '<div>
			<div>
				<label for="ft_user">Username: </label><input type="text" size="15" name="ft_user" id="ft_user" />
			</div>
			<div>
				<label for="ft_pass">Password: </label><input type="password" size="15" name="ft_pass" id="ft_pass" />
				<input type="hidden" name="act" value="dologin" />
				<input type="submit" value="Login" />
			</div>
		</div>
	</form>';
	return $str;
}
function makeHeader() { // The header showing which directory is being displayed and the navigation links.
	$str = "<h1 id='title'>".makeLink("Files in:", '', "Go to home folder")." ";
	if (empty($_REQUEST['dir'])) {
		$str .= "/</h1>";
	} else {
		// Get breadcrumbs.
		if (!empty($_REQUEST['dir'])) {
			$crumbs = explode("/", $_REQUEST['dir']);
			// Remove first empty element.
			unset($crumbs[0]);
			// Output breadcrumbs.
			$path = "";
			foreach ($crumbs as $c) {
				$path .= "/{$c}";
				$str .= "/";
				$str .= makeLink($c, "dir=".$path, "Go to folder");;
			}
		}
		$str .= "</h1>";		
	}
	// Display logout link.
	$str .= '<p id="logout">'.makeLink("[logout]", "act=logout", "Logout of File Thingie").'</p>';
	return $str;
}
function makeStatus() { // Displays status messages.
	$msg['dirfail'] = "Could not open directory.";
	$msg['mkdir'] = "Directory created.";
	$msg['mkdirfail'] = "Directory could not be created.";
	$msg['uploadfail'] = "Upload failed.";
	$msg['rename'] = "{$_REQUEST['old']} was renamed to {$_REQUEST['new']}";
	$msg['renamefail'] = "{$_REQUEST['old']} could not be renamed.";
	$msg['renamewritefail'] = "{$_REQUEST['old']} could not be renamed (write failed).";
	$msg['renametypefail'] = "{$_REQUEST['old']} was not renamed to {$_REQUEST['new']} (type not allowed).";
	$msg['renameemptyfail'] = "File could not be renamed since you didn't specify a new name.";
	$msg['renamedestfail'] = "File could not be renamed to {$new} since it already exists.";
	$msg['duplicate'] = "{$_REQUEST['old']} was duplicated to {$_REQUEST['new']}";
	$msg['duplicatefail'] = "{$_REQUEST['old']} could not be duplicated.";
	$msg['duplicatewritefail'] = "{$_REQUEST['old']} could not be duplicated (write failed).";
	$msg['duplicatetypefail'] = "{$_REQUEST['old']} was not duplicated to {$_REQUEST['new']} (type not allowed).";
	$msg['duplicateemptyfail'] = "File could not be duplicated since you didn't specify a new name.";
	$msg['duplicatedestfail'] = "File could not be duplicated to {$new} since it already exists.";
	$msg['rm'] = "{$_REQUEST['old']} deleted.";
	$msg['rmfail'] = "{$_REQUEST['old']} could not be deleted.";
	$msg['rmdir'] = "{$_REQUEST['old']} deleted.";
	$msg['rmdirfail'] = "{$_REQUEST['old']} could not be deleted.";
	$msg['move'] = "{$_REQUEST['old']} was moved to {$_REQUEST['new']}";
	$msg['movefail'] = "{$_REQUEST['old']} could not be moved.";
	$msg['movedestfail'] = "Could not move file. {$_REQUEST['old']} does not exist or is nota directory.";
	$msg['movesourcefail'] = "{$_REQUEST['old']} could not be moved. It doesn't exist.";
	$msg['movelevelfail'] = "{$_REQUEST['old']} could not be moved outside the base directory.";
	$msg['edit'] = "{$_REQUEST['old']} was saved.";
	$msg['editfilefail'] = "{$_REQUEST['old']} could not be edited.";
	$msg['edittypefail'] = "Could not edit file. This file type is not editable.";
	if ($_REQUEST['status'] == "upload") {
		// Display upload results.
		$errortypes = array("File couldn't be moved", "File type not allowed", "The file was too large", "Partial upload. Try again", "No file was uploaded. Please try again", "Unknown error", "File already exists");
		
		$str = "";
		$oklist = explode(";", $_REQUEST['oklist']);
		$errorlist = explode(";", $_REQUEST['errorlist']);
		$errortype = explode(";", $_REQUEST['errortype']);
		if (!empty($_REQUEST['oklist']) && is_array($oklist) && count($oklist) > 0) {
			$str .= "<ul>";
			foreach ($oklist as $c) {
				$str .= "<li class='ok'><strong>{$c} was uploaded.</strong></li>";
			}
			$str .= "</ul>";
		}
		if (!empty($_REQUEST['errorlist']) && is_array($errorlist) && count($errorlist) > 0) {
			$str .= "<ul>";
			foreach ($errorlist as $k => $c) {
				$str .= "<li class='error'>{$c} was not uploaded ({$errortypes[$errortype[$k]]})</li>";
			}
			$str .= "</ul>";
		}
		return $str;
	} elseif (array_key_exists($_REQUEST['status'], $msg)) {
		if (strstr($_REQUEST['status'], "fail")) {
			return "<p class=\"error\">{$msg[$_REQUEST['status']]}</p>";
		} else {
			return "<p class=\"ok\">{$msg[$_REQUEST['status']]}</p>";
		}
	} else {
		return "";
	}
}
function makeBody() { // The main body, contains either a file list or an edit form.
	$str = "";
	if (empty($_REQUEST['act'])) { // No action set - we show a list of files.
		$files = getFileList(getDir());
		if (!is_array($files)) { 
			// List couldn't be fetched. Throw error.
			redirect("status=dirfail");
		} else {			
			// Show list of files in a table.
			$str .= "<table id='filelist'>";
			$str .= "<thead><tr><th colspan=\"3\">File list</th>";
			$str .= "</tr></thead>";
			$str .= "<tbody>";
			if (count($files) <= 0) {
				$str .= "<tr><td colspan='3' class='error'>Directory is empty.</td></tr>";
			} else {
				$i = 0;
				$previous = $files[0]['type'];
				foreach ($files as $c) {
					if ($c['writeable']) {
						$class = "show writeable ";
					} else {
						$class = "";
					}
					if ($c['edit']) {
						$class .= " edit ";
					} else {
						$class .= "";
					}
					if ($i%2 != 0) {
						$odd = "odd";
					} else {
						$odd = "";
					}
					if ($previous != $c['type']) {
						// Insert seperator.
						$odd .= " seperator ";
					}
					$previous = $c['type'];
					$str .= "<tr class='{$c['type']} $odd'>";
					if ($c['writeable'] && DISABLEFILEACTIONS == FALSE) {
						$str .= "<td class='details'><span class='{$class}'>&loz;</span><span class='hide' style='display:none;'>&loz;</span></td>";						
					} else {
						$str .= "<td class='details'>&nbsp;</td>";
					}
					if ($c['type'] == "file"){
						$str .= "<td class='name'><a href=\"".getDir()."/{$c['name']}\">{$c['name']}</a></td><td class='size'>".niceFileSize($c['size']);
					} else {
						$str .= "<td class='name'>".makeLink($c['name'], "dir={$_REQUEST['dir']}/{$c['name']}", "Show files in this directory")."</td><td class='size'>{$c['size']} files";
					}
					// $str .= "</td><td>".date(DATEFORMAT, $c['modified'])."</td></tr>";
					$str .= "</td></tr>";
					$i++;
				}
			}
			$str .= "</tbody><tfoot><tr><td colspan=\"3\">".count($files)." files and folders</td></tr></tfoot>";
			$str .= "</table>";
		}
	} elseif ($_REQUEST['act'] == "edit") {
		$_REQUEST['file'] = trim(stripslashes($_REQUEST['file']));
		$str = "<h2>Edit file: {$_REQUEST['file']}</h2>";
		// Check that file exists and that it's writeable.
		if (is_writeable(getDir()."/".$_REQUEST['file'])) {
			// Check that filetype is editable.
			if (checkforedit($_REQUEST['file'])) {
				// Get file contents.
				$filecontent = implode ("", file(getDir()."/{$_REQUEST["file"]}"));
				$filecontent = htmlentities($filecontent);
				if (CONVERTTABS == TRUE) {
					$filecontent = str_replace("\t", "    ", $filecontent);
				}
				// Make form.
				$str .= '<form id="edit" action="'.getSelf().'" method="post">
					<div>
						<textarea cols="76" rows="20" name="filecontent">'.$filecontent.'</textarea>
					</div>
					<div>
						<input type="hidden" name="file" value="'.$_REQUEST['file'].'" />
						<input type="hidden" name="dir" value="'.$_REQUEST['dir'].'" />
						<input type="hidden" name="act" value="savefile" />
						<input type="submit" value="Save file" name="submit" />
						<input type="submit" value="Cancel" name="submit" />
						<input type="checkbox" name="convertspaces" id="convertspaces" checked="checked" /> <label for="convertspaces">Convert spaces to tabs</label>
					</div>
				</form>';
			} else {
				$str .= '<p class="error">Cannot edit file. This file type is not editable.</p>';				
			}
		} else {
			$str .= '<p class="error">Cannot edit file. It either does not exist or is not writeable.</p>';
		}
	}
	return $str;
}
function makeSidebar() { // Sidebar containing upload form and other actions.
	$str = '<div id="sidebar">';	
	$status .= makeStatus();
	if (empty($status)) {
		$str .= "<div id='status' class='hidden'></div>";
	} else {
		$str .= "<div id='status' class='section'><h2>Results</h2>{$status}</div>";		
	}
	if (DISABLEUPLOAD == FALSE) {
	$str .= '
	<div class="section">
		<h2>Upload files</h2>
		<form action="'.getSelf().'" method="post" enctype="multipart/form-data">
			<div id="uploadsection">
				<input type="file" class="upload" name="localfile" id="localfile-0" size="12" />
				<input type="hidden" name="act" value="upload" />
				<input type="hidden" name="MAX_FILE_SIZE" value="'.MAXSIZE.'" />
				<input type="hidden" name="dir" value="'.$_REQUEST['dir'].'" />
			</div>
			<div id="uploadbutton">
				<input type="submit" name="submit" value="Upload" />
			</div>
		</form>
	</div>
	<div class="section">
		<h2>Create folder</h2>
		<form action="'.getSelf().'" method="post">
			<div>
				<input type="text" name="mkdir" id="mkdir" size="16" />
				<input type="hidden" name="act" value="mkdir" />
				<input type="hidden" name="dir" value="'.$_REQUEST['dir'].'" />
				<input type="submit" id="mkdirsubmit" name="submit" value="Ok" />
			</div>
		</form>
	</div>';
	}
	$str .= '</div>';
	return $str;
}
function makeFooter() {
	return "<div id=\"footer\"><p><a href=\"http://www.solitude.dk/filethingie/\">File Thingie</a> &copy; <!-- Copyright --> 2003-2006 <a href=\"http://www.solitude.dk\">Andreas Haugstrup Pedersen</a>.</p></div>";
}



# Start running File Thingie #
session_start();
header ("Content-Type: text/html; charset=ISO-8859-1");
$str = "";
if (checklogin()) {
	// Run initializing functions.
	sanitizeREQUEST();
	doAction();
	$str = makeHeader();
	$str .= makeSidebar();
	$str .= makeBody();
} else {
	$str .= makeLogin();
}
$str .= makeFooter();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">


<?php require ("../includes/globals.php");?>
<?php
session_start();
//$id = $_SESSION['valid_admin'];
//$pw = $_SESSION['pw'];
$error = '<font color="#0000FF"><strong>You must be logged in to view this page</strong></font>';
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>BenFund</title>
	<link rel="author" href="http://www.solitude.dk/" title="Andreas Haugstrup Pedersen" />
	<link rel="home" href="<?php echo getSelf();?>" title="Go to home directory" />
<script type="text/javascript">
/* START jQuery */
eval(function(p,a,c,k,e,d){e=function(c){return(c<a?"":e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--){d[e(c)]=k[c]||e(c)}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('l(1T 1z.6=="Q"){1z.Q=1z.Q;u 6=q(a,c){l(a&&1T a=="q"&&6.C.1W)v 6(17).1W(a);a=a||6.1o||17;l(a.3E)v 6(6.1X(a,[]));l(c&&c.3E)v 6(c).1V(a);l(1z==7)v 1h 6(a,c);l(a.O==1C){u m=/^[^<]*(<.+>)[^>]*$/.3d(a);l(m)a=6.3D([m[1]])}7.1n(a.O==2z||a.D&&!a.1R&&a[0]!=Q&&a[0].1R?6.1X(a,[]):6.1V(a,c));u C=19[19.D-1];l(C&&1T C=="q")7.W(C);v 7};l(1T $!="Q")6.44$=$;u $=6;6.C=6.8b={3E:"1.0.3",5J:q(){v 7.D},1n:q(23){l(23&&23.O==2z){7.D=0;[].1k.16(7,23);v 7}G v 23==Q?6.1X(7,[]):7[23]},W:q(C,1g){v 6.W(7,C,1g)},8g:q(15){u 2j=-1;7.W(q(i){l(7==15)2j=i});v 2j},1t:q(1L,Y,B){v 1L.O!=1C||Y!=Q?7.W(q(){l(Y==Q)I(u E 1r 1L)6.1t(B?7.1a:7,E,1L[E]);G 6.1t(B?7.1a:7,1L,Y)}):6[B||"1t"](7[0],1L)},1f:q(1L,Y){v 7.1t(1L,Y,"26")},2B:q(e){e=e||7;u t="";I(u j=0;j<e.D;j++){u r=e[j].2f;I(u i=0;i<r.D;i++)l(r[i].1R!=8)t+=r[i].1R!=1?r[i].4Z:6.C.2B([r[i]])}v t},1Y:q(){u a=6.3D(19);v 7.W(q(){u b=a[0].3f(T);7.1i.2Y(b,7);24(b.2a)b=b.2a;b.4e(7)})},5g:q(){v 7.2T(19,T,1,q(a){7.4e(a)})},5h:q(){v 7.2T(19,T,-1,q(a){7.2Y(a,7.2a)})},5i:q(){v 7.2T(19,U,1,q(a){7.1i.2Y(a,7)})},5j:q(){v 7.2T(19,U,-1,q(a){7.1i.2Y(a,7.8j)})},4q:q(){v 7.1n(7.33.8k())},1V:q(t){v 7.2n(6.2r(7,q(a){v 6.1V(t,a)}),19)},4f:q(4D){v 7.2n(6.2r(7,q(a){v a.3f(4D!=Q?4D:T)}),19)},1c:q(t){v 7.2n(t.O==2z&&6.2r(7,q(a){I(u i=0;i<t.D;i++)l(6.1c(t[i],[a]).r.D)v a;v U})||t.O==8l&&(t?7.1n():[])||1T t=="q"&&6.2O(7,t)||6.1c(t,7).r,19)},2t:q(t){v 7.2n(t.O==1C?6.1c(t,7,U).r:6.2O(7,q(a){v a!=t}),19)},2g:q(t){v 7.2n(6.1X(7,t.O==1C?6.1V(t):t.O==2z?t:[t]),19)},4E:q(2u){v 2u?6.1c(2u,7).r.D>0:U},2T:q(1g,22,2X,C){u 4f=7.5J()>1;u a=6.3D(1g);v 7.W(q(){u 15=7;l(22&&7.2p.2b()=="8m"&&a[0].2p.2b()!="62"){u 29=7.4S("29");l(!29.D){15=17.5N("29");7.4e(15)}G 15=29[0]}I(u i=(2X<0?a.D-1:0);i!=(2X<0?2X:a.D);i+=2X){C.16(15,[4f?a[i].3f(T):a[i]])}})},2n:q(a,1g){u C=1g&&1g[1g.D-1];u 2d=1g&&1g[1g.D-2];l(C&&C.O!=1v)C=M;l(2d&&2d.O!=1v)2d=M;l(!C){l(!7.33)7.33=[];7.33.1k(7.1n());7.1n(a)}G{u 1Z=7.1n();7.1n(a);l(2d&&a.D||!2d)7.W(2d||C).1n(1Z);G 7.1n(1Z).W(C)}v 7}};6.1y=6.C.1y=q(15,E){l(19.D>1&&(E===M||E==Q))v 15;l(!E){E=15;15=7}I(u i 1r E)15[i]=E[i];v 15};6.1y({5C:q(){6.65=T;6.W(6.2e.5r,q(i,n){6.C[i]=q(a){u L=6.2r(7,n);l(a&&a.O==1C)L=6.1c(a,L).r;v 7.2n(L,19)}});6.W(6.2e.2o,q(i,n){6.C[i]=q(){u a=19;v 7.W(q(){I(u j=0;j<a.D;j++)6(a[j])[n](7)})}});6.W(6.2e.W,q(i,n){6.C[i]=q(){v 7.W(n,19)}});6.W(6.2e.1c,q(i,n){6.C[n]=q(23,C){v 7.1c(":"+n+"("+23+")",C)}});6.W(6.2e.1t,q(i,n){n=n||i;6.C[i]=q(h){v h==Q?7.D?7[0][n]:M:7.1t(n,h)}});6.W(6.2e.1f,q(i,n){6.C[n]=q(h){v h==Q?(7.D?6.1f(7[0],n):M):7.1f(n,h)}})},W:q(15,C,1g){l(15.D==Q)I(u i 1r 15)C.16(15[i],1g||[i,15[i]]);G I(u i=0;i<15.D;i++)l(C.16(15[i],1g||[i,15[i]])===U)45;v 15},1j:{2g:q(o,c){l(6.1j.3t(o,c))v;o.1j+=(o.1j?" ":"")+c},25:q(o,c){l(!c){o.1j=""}G{u 2L=o.1j.3b(" ");I(u i=0;i<2L.D;i++){l(2L[i]==c){2L.67(i,1);45}}o.1j=2L.5Z(\' \')}},3t:q(e,a){l(e.1j!=Q)e=e.1j;v 1h 43("(^|\\\\s)"+a+"(\\\\s|$)").28(e)}},4A:q(e,o,f){I(u i 1r o){e.1a["1Z"+i]=e.1a[i];e.1a[i]=o[i]}f.16(e,[]);I(u i 1r o)e.1a[i]=e.1a["1Z"+i]},1f:q(e,p){l(p=="1G"||p=="2c"){u 1Z={},3K,3F,d=["68","6O","69","7c"];I(u i 1r d){1Z["6b"+d[i]]=0;1Z["6c"+d[i]+"6e"]=0}6.4A(e,1Z,q(){l(6.1f(e,"1u")!="20"){3K=e.6f;3F=e.6g}G{e=6(e.3f(T)).1V(":3W").5u("2J").4q().1f({3U:"1S",2H:"6i",1u:"2F",6j:"0",5l:"0"}).4H(e.1i)[0];u 31=6.1f(e.1i,"2H");l(31==""||31=="3R")e.1i.1a.2H="6k";3K=e.6l;3F=e.6m;l(31==""||31=="3R")e.1i.1a.2H="3R";e.1i.3s(e)}});v p=="1G"?3K:3F}v 6.26(e,p)},26:q(F,E,4I){u L;l(E==\'1m\'&&6.11.1p)v 6.1t(F.1a,\'1m\');l(E=="3p"||E=="2y")E=6.11.1p?"37":"2y";l(!4I&&F.1a[E]){L=F.1a[E]}G l(F.34){u 5S=E.1B(/\\-(\\w)/g,q(m,c){v c.2b()});L=F.34[E]||F.34[5S]}G l(17.3g&&17.3g.4u){l(E=="2y"||E=="37")E="3p";E=E.1B(/([A-Z])/g,"-$1").4d();u 1l=17.3g.4u(F,M);l(1l)L=1l.5P(E);G l(E==\'1u\')L=\'20\';G 6.4A(F,{1u:\'2F\'},q(){L=17.3g.4u(7,M).5P(E)})}v L},3D:q(a){u r=[];I(u i=0;i<a.D;i++){u 1M=a[i];l(1M.O==1C){u s=6.2K(1M),21=17.5N("21"),1Y=[0,"",""];l(!s.1b("<6v"))1Y=[1,"<3c>","</3c>"];G l(!s.1b("<6w")||!s.1b("<29"))1Y=[1,"<22>","</22>"];G l(!s.1b("<4t"))1Y=[2,"<22>","</22>"];G l(!s.1b("<6x")||!s.1b("<6z"))1Y=[3,"<22><29><4t>","</4t></29></22>"];21.2V=1Y[1]+s+1Y[2];24(1Y[0]--)21=21.2a;I(u j=0;j<21.2f.D;j++)r.1k(21.2f[j])}G l(1M.D!=Q&&!1M.1R)I(u n=0;n<1M.D;n++)r.1k(1M[n]);G r.1k(1M.1R?1M:17.6A(1M.6C()))}v r},2u:{"":"m[2]== \'*\'||a.2p.2b()==m[2].2b()","#":"a.3a(\'3H\')&&a.3a(\'3H\')==m[2]",":":{5o:"i<m[3]-0",5X:"i>m[3]-0",5L:"m[3]-0==i",5n:"m[3]-0==i",2h:"i==0",1N:"i==r.D-1",52:"i%2==0",53:"i%2","5L-3x":"6.1x(a,m[3]).1l","2h-3x":"6.1x(a,0).1l","1N-3x":"6.1x(a,0).1N","6D-3x":"6.1x(a).D==1",5s:"a.2f.D",5B:"!a.2f.D",5p:"6.C.2B.16([a]).1b(m[3])>=0",6E:"a.B!=\'1S\'&&6.1f(a,\'1u\')!=\'20\'&&6.1f(a,\'3U\')!=\'1S\'",1S:"a.B==\'1S\'||6.1f(a,\'1u\')==\'20\'||6.1f(a,\'3U\')==\'1S\'",6F:"!a.2P",2P:"a.2P",2J:"a.2J",3V:"a.3V || 6.1t(a, \'3V\')",2B:"a.B==\'2B\'",3W:"a.B==\'3W\'",5y:"a.B==\'5y\'",3Q:"a.B==\'3Q\'",5v:"a.B==\'5v\'",4x:"a.B==\'4x\'",5w:"a.B==\'5w\'",4w:"a.B==\'4w\'",4s:"a.B==\'4s\'",5z:"a.2p.4d().4T(/5z|3c|6L|4s/)"},".":"6.1j.3t(a,m[2])","@":{"=":"z==m[4]","!=":"z!=m[4]","^=":"z && !z.1b(m[4])","$=":"z && z.32(z.D - m[4].D,m[4].D)==m[4]","*=":"z && z.1b(m[4])>=0","":"z"},"[":"6.1V(m[2],a).D"},3B:["\\\\.\\\\.|/\\\\.\\\\.","a.1i",">|/","6.1x(a.2a)","\\\\+","6.1x(a).3z","~",q(a){u r=[];u s=6.1x(a);l(s.n>0)I(u i=s.n;i<s.D;i++)r.1k(s[i]);v r}],1V:q(t,1o){l(1o&&1o.1R==Q)1o=M;1o=1o||6.1o||17;l(t.O!=1C)v[t];l(!t.1b("//")){1o=1o.4Q;t=t.32(2,t.D)}G l(!t.1b("/")){1o=1o.4Q;t=t.32(1,t.D);l(t.1b("/")>=1)t=t.32(t.1b("/"),t.D)}u L=[1o];u 1K=[];u 1N=M;24(t.D>0&&1N!=t){u r=[];1N=t;t=6.2K(t).1B(/^\\/\\//i,"");u 36=U;I(u i=0;i<6.3B.D;i+=2){l(36)51;u 2v=1h 43("^("+6.3B[i]+")");u m=2v.3d(t);l(m){r=L=6.2r(L,6.3B[i+1]);t=6.2K(t.1B(2v,""));36=T}}l(!36){l(!t.1b(",")||!t.1b("|")){l(L[0]==1o)L.4h();1K=6.1X(1K,L);r=L=[1o];t=" "+t.32(1,t.D)}G{u 3Z=/^([#.]?)([a-4Y-9\\\\*44-]*)/i;u m=3Z.3d(t);l(m[1]=="#"){u 4l=17.5V(m[2]);r=L=4l?[4l]:[];t=t.1B(3Z,"")}G{l(!m[2]||m[1]==".")m[2]="*";I(u i=0;i<L.D;i++)r=6.1X(r,m[2]=="*"?6.40(L[i]):L[i].4S(m[2]))}}}l(t){u 1D=6.1c(t,r);L=r=1D.r;t=6.2K(1D.t)}}l(L&&L[0]==1o)L.4h();1K=6.1X(1K,L);v 1K},40:q(o,r){r=r||[];u s=o.2f;I(u i=0;i<s.D;i++)l(s[i].1R==1){r.1k(s[i]);6.40(s[i],r)}v r},1t:q(F,1d,Y){u 2m={"I":"7v","6P":"1j","3p":6.11.1p?"37":"2y",2y:6.11.1p?"37":"2y",2V:"2V",1j:"1j",Y:"Y",2P:"2P",2J:"2J",6R:"6S"};l(1d=="1m"&&6.11.1p&&Y!=Q){F[\'6U\']=1;l(Y==1)v F["1c"]=F["1c"].1B(/3k\\([^\\)]*\\)/5c,"");G v F["1c"]=F["1c"].1B(/3k\\([^\\)]*\\)/5c,"")+"3k(1m="+Y*4U+")"}G l(1d=="1m"&&6.11.1p){v F["1c"]?4c(F["1c"].4T(/3k\\(1m=(.*)\\)/)[1])/4U:1}l(1d=="1m"&&6.11.2I&&Y==1)Y=0.6W;l(2m[1d]){l(Y!=Q)F[2m[1d]]=Y;v F[2m[1d]]}G l(Y==Q&&6.11.1p&&F.2p&&F.2p.2b()==\'6X\'&&(1d==\'7f\'||1d==\'7e\')){v F.70(1d).4Z}G l(F.3a!=Q&&F.7b){l(Y!=Q)F.72(1d,Y);v F.3a(1d)}G{1d=1d.1B(/-([a-z])/73,q(z,b){v b.2b()});l(Y!=Q)F[1d]=Y;v F[1d]}},4X:["\\\\[ *(@)S *([!*$^=]*) *(\'?\\"?)(.*?)\\\\4 *\\\\]","(\\\\[)\\s*(.*?)\\s*\\\\]","(:)S\\\\(\\"?\'?([^\\\\)]*?)\\"?\'?\\\\)","([:.#]*)S"],1c:q(t,r,2t){u g=2t!==U?6.2O:q(a,f){v 6.2O(a,f,T)};24(t&&/^[a-z[({<*:.#]/i.28(t)){u p=6.4X;I(u i=0;i<p.D;i++){u 2v=1h 43("^"+p[i].1B("S","([a-z*44-][a-4Y-76-]*)"),"i");u m=2v.3d(t);l(m){l(!i)m=["",m[1],m[3],m[2],m[5]];t=t.1B(2v,"");45}}l(m[1]==":"&&m[2]=="2t")r=6.1c(m[3],r,U).r;G{u f=6.2u[m[1]];l(f.O!=1C)f=6.2u[m[1]][m[2]];3A("f = q(a,i){"+(m[1]=="@"?"z=6.1t(a,m[3]);":"")+"v "+f+"}");r=g(r,f)}}v{r:r,t:t}},2K:q(t){v t.1B(/^\\s+|\\s+$/g,"")},3L:q(F){u 47=[];u 1l=F.1i;24(1l&&1l!=17){47.1k(1l);1l=1l.1i}v 47},1x:q(F,2j,2t){u 14=[];l(F){u 2k=F.1i.2f;I(u i=0;i<2k.D;i++){l(2t===T&&2k[i]==F)51;l(2k[i].1R==1)14.1k(2k[i]);l(2k[i]==F)14.n=14.D-1}}v 6.1y(14,{1N:14.n==14.D-1,1l:2j=="52"&&14.n%2==0||2j=="53"&&14.n%2||14[2j]==F,4j:14[14.n-1],3z:14[14.n+1]})},1X:q(2h,35){u 1J=[];I(u k=0;k<2h.D;k++)1J[k]=2h[k];I(u i=0;i<35.D;i++){u 48=T;I(u j=0;j<2h.D;j++)l(35[i]==2h[j])48=U;l(48)1J.1k(35[i])}v 1J},2O:q(14,C,4a){l(C.O==1C)C=1h 1v("a","i","v "+C);u 1J=[];I(u i=0;i<14.D;i++)l(!4a&&C(14[i],i)||4a&&!C(14[i],i))1J.1k(14[i]);v 1J},2r:q(14,C){l(C.O==1C)C=1h 1v("a","v "+C);u 1J=[];I(u i=0;i<14.D;i++){u 1D=C(14[i],i);l(1D!==M&&1D!=Q){l(1D.O!=2z)1D=[1D];1J=6.1X(1J,1D)}}v 1J},J:{2g:q(P,B,1H){l(6.11.1p&&P.42!=Q)P=1z;l(!1H.2q)1H.2q=7.2q++;l(!P.1E)P.1E={};u 2W=P.1E[B];l(!2W){2W=P.1E[B]={};l(P["2N"+B])2W[0]=P["2N"+B]}2W[1H.2q]=1H;P["2N"+B]=7.58;l(!7.1e[B])7.1e[B]=[];7.1e[B].1k(P)},2q:1,1e:{},25:q(P,B,1H){l(P.1E)l(B&&P.1E[B])l(1H)57 P.1E[B][1H.2q];G I(u i 1r P.1E[B])57 P.1E[B][i];G I(u j 1r P.1E)7.25(P,j)},1P:q(B,K,P){K=K||[];l(!P){u g=7.1e[B];l(g)I(u i=0;i<g.D;i++)7.1P(B,K,g[i])}G l(P["2N"+B]){K.59(7.2m({B:B,2G:P}));P["2N"+B].16(P,K)}},58:q(J){l(1T 6=="Q")v U;J=J||6.J.2m(1z.J);l(!J)v U;u 3m=T;u c=7.1E[J.B];u 1g=[].7h.3O(19,1);1g.59(J);I(u j 1r c){l(c[j].16(7,1g)===U){J.4p();J.5a();3m=U}}v 3m},2m:q(J){l(6.11.1p){J=1z.J;J.2G=J.7i}G l(6.11.2M&&J.2G.1R==3){J=6.1y({},J);J.2G=J.2G.1i}J.4p=q(){7.3m=U};J.5a=q(){7.7l=T};v J}}});1h q(){u b=5I.5K.4d();6.11={2M:/5e/.28(b),30:/30/.28(b),1p:/1p/.28(b)&&!/30/.28(b),2I:/2I/.28(b)&&!/(7m|5e)/.28(b)};6.7n=!6.11.1p||17.7o=="7p"};6.2e={2o:{4H:"5g",7q:"5h",2Y:"5i",7r:"5j"},1f:"2c,1G,7s,5l,2H,3p,3h,7t,7u".3b(","),1c:["5n","5o","5X","5p"],1t:{1D:"Y",38:"2V",3H:M,7x:M,1d:M,7z:M,3w:M,7A:M},5r:{5s:"a.1i",7B:6.3L,3L:6.3L,3z:"6.1x(a).3z",4j:"6.1x(a).4j",2k:"6.1x(a, M, T)",7C:"6.1x(a.2a)"},W:{5u:q(1L){7.7E(1L)},1A:q(){7.1a.1u=7.2A?7.2A:"";l(6.1f(7,"1u")=="20")7.1a.1u="2F"},1s:q(){7.2A=7.2A||6.1f(7,"1u");l(7.2A=="20")7.2A="2F";7.1a.1u="20"},4o:q(){6(7)[6(7).4E(":1S")?"1A":"1s"].16(6(7),19)},7F:q(c){6.1j.2g(7,c)},7G:q(c){6.1j.25(7,c)},7H:q(c){6.1j[6.1j.3t(7,c)?"25":"2g"](7,c)},25:q(a){l(!a||6.1c(a,[7]).r)7.1i.3s(7)},5B:q(){24(7.2a)7.3s(7.2a)},2Z:q(B,C){l(C.O==1C)C=1h 1v("e",(!C.1b(".")?"6(7)":"v ")+C);6.J.2g(7,B,C)},4C:q(B,C){6.J.25(7,B,C)},1P:q(B,K){6.J.1P(B,K,7)}}};6.5C();6.C.1y({5E:6.C.4o,4o:q(a,b){v a&&b&&a.O==1v&&b.O==1v?7.5M(q(e){7.1N=7.1N==a?b:a;e.4p();v 7.1N.16(7,[e])||U}):7.5E.16(7,19)},7K:q(f,g){q 4r(e){u p=(e.B=="3C"?e.7M:e.7N)||e.7O;24(p&&p!=7)3u{p=p.1i}3o(e){p=7};l(p==7)v U;v(e.B=="3C"?f:g).16(7,[e])}v 7.3C(4r).5Q(4r)},1W:q(f){l(6.3y)f.16(17);G{6.2C.1k(f)}v 7}});6.1y({3y:U,2C:[],1W:q(){l(!6.3y){6.3y=T;l(6.2C){I(u i=0;i<6.2C.D;i++)6.2C[i].16(17);6.2C=M}l(6.11.2I||6.11.30)17.7P("5T",6.1W,U)}}});1h q(){u e=("7R,7S,2S,7T,7U,4z,5M,7V,"+"7X,7Y,81,3C,5Q,83,4w,3c,"+"4x,86,87,88,2l").3b(",");I(u i=0;i<e.D;i++)1h q(){u o=e[i];6.C[o]=q(f){v f?7.2Z(o,f):7.1P(o)};6.C["89"+o]=q(f){v 7.4C(o,f)};6.C["8a"+o]=q(f){u P=6(7);u 1H=q(){P.4C(o,1H);P=M;f.16(7,19)};v 7.2Z(o,1H)}};l(6.11.2I||6.11.30){17.8c("5T",6.1W,U)}G l(6.11.1p){17.8d("<8e"+"8f 3H=5W 8n=T "+"3w=//:><\\/27>");u 27=17.5V("5W");27.2w=q(){l(7.3n!="1I")v;7.1i.3s(7);6.1W()};27=M}G l(6.11.2M){6.3N=42(q(){l(17.3n=="63"||17.3n=="1I"){56(6.3N);6.3N=M;6.1W()}},10)}6.J.2g(1z,"2S",6.1W)};l(6.11.1p)6(1z).4z(q(){u J=6.J,1e=J.1e;I(u B 1r 1e){u 3P=1e[B],i=3P.D;l(i>0)6a l(B!=\'4z\')J.25(3P[i-1],B);24(--i)}});6.C.1y({60:6.C.1A,1A:q(12,H){v 12?7.1U({1G:"1A",2c:"1A",1m:"1A"},12,H):7.60()},5U:6.C.1s,1s:q(12,H){v 12?7.1U({1G:"1s",2c:"1s",1m:"1s"},12,H):7.5U()},6n:q(12,H){v 7.1U({1G:"1A"},12,H)},6o:q(12,H){v 7.1U({1G:"1s"},12,H)},6p:q(12,H){v 7.W(q(){u 4J=6(7).4E(":1S")?"1A":"1s";6(7).1U({1G:4J},12,H)})},6r:q(12,H){v 7.1U({1m:"1A"},12,H)},6s:q(12,H){v 7.1U({1m:"1s"},12,H)},6t:q(12,2o,H){v 7.1U({1m:2o},12,H)},1U:q(E,12,H){v 7.1w(q(){7.2U=6.1y({},E);I(u p 1r E){u e=1h 6.2R(7,6.12(12,H),p);l(E[p].O==4O)e.3e(e.1l(),E[p]);G e[E[p]](E)}})},1w:q(B,C){l(!C){C=B;B="2R"}v 7.W(q(){l(!7.1w)7.1w={};l(!7.1w[B])7.1w[B]=[];7.1w[B].1k(C);l(7.1w[B].D==1)C.16(7)})}});6.1y({5f:q(e,p){l(e.5F)v;l(p=="1G"&&e.4L!=3l(6.26(e,p)))v;l(p=="2c"&&e.4M!=3l(6.26(e,p)))v;u a=e.1a[p];u o=6.26(e,p,1);l(p=="1G"&&e.4L!=o||p=="2c"&&e.4M!=o)v;e.1a[p]=e.34?"":"5H";u n=6.26(e,p,1);l(o!=n&&n!="5H"){e.1a[p]=a;e.5F=T}},12:q(s,o){o=o||{};l(o.O==1v)o={1I:o};u 5D={6G:6H,6J:4K};o.2E=(s&&s.O==4O?s:5D[s])||5k;o.3J=o.1I;o.1I=q(){6.4R(7,"2R");l(o.3J&&o.3J.O==1v)o.3J.16(7)};v o},1w:{},4R:q(F,B){B=B||"2R";l(F.1w&&F.1w[B]){F.1w[B].4h();u f=F.1w[B][0];l(f)f.16(F)}},2R:q(F,2x,E){u z=7;z.o={2E:2x.2E||5k,1I:2x.1I,2s:2x.2s};z.V=F;u y=z.V.1a;z.a=q(){l(2x.2s)2x.2s.16(F,[z.2i]);l(E=="1m")6.1t(y,"1m",z.2i);G l(3l(z.2i))y[E]=3l(z.2i)+"5d";y.1u="2F"};z.61=q(){v 4c(6.1f(z.V,E))};z.1l=q(){u r=4c(6.26(z.V,E));v r&&r>-6Z?r:z.61()};z.3e=q(41,2o){z.3M=(1h 54()).55();z.2i=41;z.a();z.49=42(q(){z.2s(41,2o)},13)};z.1A=q(){l(!z.V.1Q)z.V.1Q={};z.V.1Q[E]=7.1l();z.3e(0,z.V.1Q[E]);l(E!="1m")y[E]="77"};z.1s=q(){l(!z.V.1Q)z.V.1Q={};z.V.1Q[E]=7.1l();z.o.1s=T;z.3e(z.V.1Q[E],0)};l(!z.V.4b)z.V.4b=6.1f(z.V,"3h");y.3h="1S";z.2s=q(4B,4g){u t=(1h 54()).55();l(t>z.o.2E+z.3M){56(z.49);z.49=M;z.2i=4g;z.a();z.V.2U[E]=T;u 1K=T;I(u i 1r z.V.2U)l(z.V.2U[i]!==T)1K=U;l(1K){y.3h=z.V.4b;l(z.o.1s)y.1u=\'20\';l(z.o.1s){I(u p 1r z.V.2U){l(p=="1m")6.1t(y,p,z.V.1Q[p]);G y[p]=z.V.1Q[p]+"5d";l(p==\'1G\'||p==\'2c\')6.5f(z.V,p)}}}l(1K&&z.o.1I&&z.o.1I.O==1v)z.o.1I.16(z.V)}G{u p=(t-7.3M)/z.o.2E;z.2i=((-5q.7w(p*5q.7y)/2)+0.5)*(4g-4B)+4B;z.a()}}}});6.C.1y({7D:q(N,1O,H){7.2S(N,1O,H,1)},2S:q(N,1O,H,1F){l(N.O==1v)v 7.2Z("2S",N);H=H||q(){};u B="3T";l(1O){l(1O.O==1v){H=1O;1O=M}G{1O=6.2Q(1O);B="4W"}}u 4m=7;6.3I(B,N,1O,q(3v,18){l(18=="2D"||!1F&&18=="5m"){4m.38(3v.3G).3X().W(H,[3v.3G,18])}G H.16(4m,[3v.3G,18])},1F);v 7},7J:q(){v 6.2Q(7)},3X:q(){v 7.1V(\'27\').W(q(){l(7.3w)6.5Y(7.3w,q(){});G 3A.3O(1z,7.2B||7.7L||7.2V||"")}).4q()}});l(6.11.1p&&1T 3i=="Q")3i=q(){v 1h 7Q(5I.5K.1b("7W 5")>=0?"82.5R":"84.5R")};1h q(){u e="5O,5G,5A,5x,5t".3b(",");I(u i=0;i<e.D;i++)1h q(){u o=e[i];6.C[o]=q(f){v 7.2Z(o,f)}}};6.1y({1n:q(N,K,H,B,1F){l(K&&K.O==1v){B=H;H=K;K=M}l(K)N+=((N.1b("?")>-1)?"&":"?")+6.2Q(K);6.3I("3T",N,M,q(r,18){l(H)H(6.3r(r,B),18)},1F)},8h:q(N,K,H,B){6.1n(N,K,H,B,1)},5Y:q(N,H){l(H)6.1n(N,M,H,"27");G{6.1n(N,M,M,"27")}},64:q(N,K,H){l(H)6.1n(N,K,H,"3S");G{6.1n(N,K,"3S")}},8o:q(N,K,H,B){6.3I("4W",N,6.2Q(K),q(r,18){l(H)H(6.3r(r,B),18)})},1q:0,6h:q(1q){6.1q=1q},39:{},3I:q(B,N,K,L,1F){u 1e=T;u 1q=6.1q;l(!N){L=B.1I;u 2D=B.2D;u 2l=B.2l;u 4k=B.4k;u 1e=1T B.1e=="6q"?B.1e:T;u 1q=1T B.1q=="6u"?B.1q:6.1q;1F=B.1F||U;K=B.K;N=B.N;B=B.B}l(1e&&!6.4v++)6.J.1P("5O");u 4y=U;u R=1h 3i();R.6B(B||"3T",N,T);l(K)R.3j("6I-6K","6M/x-6N-6Q-6T");l(1F)R.3j("6V-3Y-6Y",6.39[N]||"71, 74 75 78 46:46:46 79");R.3j("X-7a-7d","3i");l(R.7g)R.3j("7j","7k");u 2w=q(4F){l(R&&(R.3n==4||4F=="1q")){4y=T;u 18=6.4G(R)&&4F!="1q"?1F&&6.4N(R,N)?"5m":"2D":"2l";l(18!="2l"){u 3q;3u{3q=R.4i("4P-3Y")}3o(e){}l(1F&&3q)6.39[N]=3q;l(2D)2D(6.3r(R,4k),18);l(1e)6.J.1P("5t")}G{l(2l)2l(R,18);l(1e)6.J.1P("5x")}l(1e)6.J.1P("5A");l(1e&&!--6.4v)6.J.1P("5G");l(L)L(R,18);R.2w=q(){};R=M}};R.2w=2w;l(1q>0)7Z(q(){l(R){R.85();l(!4y)2w("1q");R=M}},1q);R.8i(K)},4v:0,4G:q(r){3u{v!r.18&&66.6d=="3Q:"||(r.18>=4K&&r.18<6y)||r.18==5b||6.11.2M&&r.18==Q}3o(e){}v U},4N:q(R,N){3u{u 4V=R.4i("4P-3Y");v R.18==5b||4V==6.39[N]||6.11.2M&&R.18==Q}3o(e){}v U},3r:q(r,B){u 4n=r.4i("7I-B");u K=!B&&4n&&4n.1b("R")>=0;K=B=="R"||K?r.80:r.3G;l(B=="27")3A.3O(1z,K);l(B=="3S")3A("K = "+K);l(B=="38")$("<21>").38(K).3X();v K},2Q:q(a){u s=[];l(a.O==2z||a.3E){I(u i=0;i<a.D;i++)s.1k(a[i].1d+"="+50(a[i].Y))}G{I(u j 1r a)s.1k(j+"="+50(a[j]))}v s.5Z("&")}})}',62,521,'||||||jQuery|this||||||||||||||if|||||function||||var|return||||||type|fn|length|prop|elem|else|callback|for|event|data|ret|null|url|constructor|element|undefined|xml||true|false|el|each||value|||browser|speed||elems|obj|apply|document|status|arguments|style|indexOf|filter|name|global|css|args|new|parentNode|className|push|cur|opacity|get|context|msie|timeout|in|hide|attr|display|Function|queue|sibling|extend|window|show|replace|String|val|events|ifModified|height|handler|complete|result|done|key|arg|last|params|trigger|orig|nodeType|hidden|typeof|animate|find|ready|merge|wrap|old|none|div|table|num|while|remove|curCSS|script|test|tbody|firstChild|toUpperCase|width|fn2|macros|childNodes|add|first|now|pos|siblings|error|fix|pushStack|to|nodeName|guid|map|step|not|expr|re|onreadystatechange|options|cssFloat|Array|oldblock|text|readyList|success|duration|block|target|position|mozilla|checked|trim|classes|safari|on|grep|disabled|param|fx|load|domManip|curAnim|innerHTML|handlers|dir|insertBefore|bind|opera|parPos|substr|stack|currentStyle|second|foundToken|styleFloat|html|lastModified|getAttribute|split|select|exec|custom|cloneNode|defaultView|overflow|XMLHttpRequest|setRequestHeader|alpha|parseInt|returnValue|readyState|catch|float|modRes|httpData|removeChild|has|try|res|src|child|isReady|next|eval|token|mouseover|clean|jquery|oWidth|responseText|id|ajax|oldComplete|oHeight|parents|startTime|safariTimer|call|els|file|static|json|GET|visibility|selected|radio|evalScripts|Modified|re2|getAll|from|setInterval|RegExp|_|break|00|matched|noCollision|timer|inv|oldOverflow|parseFloat|toLowerCase|appendChild|clone|lastNum|shift|getResponseHeader|prev|dataType|oid|self|ct|toggle|preventDefault|end|handleHover|button|tr|getComputedStyle|active|reset|submit|requestDone|unload|swap|firstNum|unbind|deep|is|istimeout|httpSuccess|appendTo|force|state|200|scrollHeight|scrollWidth|httpNotModified|Number|Last|documentElement|dequeue|getElementsByTagName|match|100|xmlRes|POST|parse|z0|nodeValue|encodeURIComponent|continue|even|odd|Date|getTime|clearInterval|delete|handle|unshift|stopPropagation|304|gi|px|webkit|setAuto|append|prepend|before|after|400|left|notmodified|eq|lt|contains|Math|axis|parent|ajaxSuccess|removeAttr|password|image|ajaxError|checkbox|input|ajaxComplete|empty|init|ss|_toggle|notAuto|ajaxStop|auto|navigator|size|userAgent|nth|click|createElement|ajaxStart|getPropertyValue|mouseout|XMLHTTP|newProp|DOMContentLoaded|_hide|getElementById|__ie_init|gt|getScript|join|_show|max|THEAD|loaded|getJSON|initDone|location|splice|Top|Right|do|padding|border|protocol|Width|offsetHeight|offsetWidth|ajaxTimeout|absolute|right|relative|clientHeight|clientWidth|slideDown|slideUp|slideToggle|boolean|fadeIn|fadeOut|fadeTo|number|opt|thead|td|300|th|createTextNode|open|toString|only|visible|enabled|slow|600|Content|fast|Type|textarea|application|www|Bottom|class|form|readonly|readOnly|urlencoded|zoom|If|9999|FORM|Since|10000|getAttributeNode|Thu|setAttribute|ig|01|Jan|9_|1px|1970|GMT|Requested|tagName|Left|With|method|action|overrideMimeType|slice|srcElement|Connection|close|cancelBubble|compatible|boxModel|compatMode|CSS1Compat|prependTo|insertAfter|top|color|background|htmlFor|cos|title|PI|href|rel|ancestors|children|loadIfModified|removeAttribute|addClass|removeClass|toggleClass|content|serialize|hover|textContent|fromElement|toElement|relatedTarget|removeEventListener|ActiveXObject|blur|focus|resize|scroll|dblclick|MSIE|mousedown|mouseup|setTimeout|responseXML|mousemove|Microsoft|change|Msxml2|abort|keydown|keypress|keyup|un|one|prototype|addEventListener|write|scr|ipt|index|getIfModified|send|nextSibling|pop|Boolean|TABLE|defer|post'.split('|'),0,{}))
/* END jQuery */
</script>
	<script type="text/javascript">
	$(document).ready(function(){
		// Set focus on login username.
		if (document.getElementById("ft_user")) {
			document.getElementById("ft_user").focus();
		}
		// Prep upload section.
		$("#localfile-0").change(function(){
			$('#uploadsection').after('<h3>Files for upload:</h3><ul id="files_list"></ul>');
			uploadCallback(this);
		});
		// Make background color on table rows show up nicely on hover
		$("#filelist td a").hover(function(){$(this).parent().parent().toggleClass('rowhover');}, function(){$(this).parent().parent().toggleClass('rowhover')});
		// Prep file details.
		$("#filelist td.details span.hide").hover(function(){$(this).toggleClass('hover')}, function(){$(this).toggleClass('hover')}).click(function(){
			$(this).parent().parent().next().remove();
			$(this).hide();
			$(this).prev().show();
		});
		$("#filelist td.details span.show").hover(function(){$(this).toggleClass('hover')}, function(){$(this).toggleClass('hover')}).click(function(){
			$(this).parent().parent().after("<tr class='filedetails'></tr>");
			if ($(this).attr("class").match("writeable") == "writeable") {
				$(this).parent().parent().next("tr.filedetails").append("<td colspan=\"3\"><ul class=\"navigation\"><li class=\"selected\">Rename</li><li>Move</li><li>Delete</li><li>Duplicate</li></ul><form method=\"post\" action=\"<?php echo getSelf();?>\"><div><label for='newvalue'>Rename to:</label><input type=\"text\" value=\""+$(this).parent().parent().find("td.name").text()+"\" size=\"18\" class='newvalue' name=\"newvalue\" /><input type=\"hidden\" value=\""+$(this).parent().parent().find("td.name").text()+"\" class='file' name=\"file\" /><input type=\"submit\" class='submit' value=\"Ok\" /><input type=\"hidden\" name=\"dir\" value=\"<?php echo $_REQUEST['dir'];?>\" /><input type=\"hidden\" name=\"act\" class=\"act\" value=\"rename\" /></div></form></td>").find("li").hover(function(){$(this).toggleClass('hover')}, function(){$(this).toggleClass('hover')}).click(showOption);
				// Focus on new value field.
				$(this).parent().parent().next("tr.filedetails").find("input.newvalue").get(0).focus();
				$(this).parent().parent().next("tr.filedetails").find("input.newvalue").get(0).select();
			}
			if ($(this).attr("class").match("edit") == "edit") {
				$(this).parent().parent().next("tr.filedetails").find("ul").append("<li class='edit'>Edit</li>").find("li.edit").hover(function(){$(this).toggleClass('hover')}, function(){$(this).toggleClass('hover')}).click(showOption);
			}
			$(this).hide();
			$(this).next().show();
		});
	});
	function showOption() { // Shows a selection from the file details menu.
		var section = $(this).text().toLowerCase();
		var act = $(this).parent().parent().find("input.act");
		var newval = $(this).parent().parent().find("input.newvalue");
		var file = $(this).parent().parent().find("input.file").val();
		var label = $(this).parent().parent().find("label");
		var submit = $(this).parent().parent().find("input.submit");
		// Set 'act' field to selected section.
		act.val($(this).text().toLowerCase());
		// Un-select all <li>
		$(this).parent().find("li").removeClass("selected");
		$(this).addClass("selected");
		// Show/hide the new value field and change the text of the submit button.
		if (section == "rename" || section == "move" || section == "duplicate") {
			// Show new value field.
			newval.show();
			label.empty();
			if (section == "rename") {
				label.append("Rename to:");
				newval.val(file);
			} else if (section == "move") {
				label.append("Move to folder:");
				newval.val("");
			} else if (section == "duplicate") {
				label.append("Duplicate to file:");
				if (file.indexOf(".") != -1) {
					newval.val(file.substring(0, file.lastIndexOf("."))+"(copy)"+file.substr(file.lastIndexOf(".")));					
				} else {
					newval.val(file+"(copy)");
				}
			}
			submit.val("Ok");
			// Set focus on new value field.
			newval.get(0).focus();
			newval.get(0).select();
		} else if (section == "delete") {
			// Hide new value field.
			newval.hide();
			label.empty();
			label.append("Do you really want to delete file?");
			submit.val("Yes, delete it");			
		} else if (section == "edit") {
			// Hide new value field.
			newval.hide();
			label.empty();
			label.append("Do you want to edit this file?");
			submit.val("Yes, edit file")
		}
	}
	function uploadCallback(obj) {
		$(obj).hide();
		// Make random number: 
		var d = new Date();
		var t = d.getTime();
		$(obj).parent().prepend('<input type="file" size="12" class="upload" name="localfile-'+t+'" id="localfile-'+t+'" />');
		$('#localfile-'+t).change(function() {uploadCallback(this)});
		if (obj.value.indexOf("/") != -1) {
			var v = obj.value.substr(obj.value.lastIndexOf("/")+1);
		} else if (obj.value.indexOf("\\") != -1) {
			var v = obj.value.substr(obj.value.lastIndexOf("\\")+1);			
		} else {
			var v = obj.value;
		}
		if(v != '') {
			$("#files_list").append('<li>'+v+' <span class="error" title="Cancel upload of this file">[x]</span></li>').find("span").click(function(){
				$(this).parent().remove();
				$(obj).remove();
				return true;
			});
		}
	};
	</script>
	<style type="text/css">
body {
	font-family:Verdana, sans-serif;
	font-size:12px;
	color:<?php echo COLOURTEXT;?>;
	background:#fff;
}
body, h1, h2, .navigation, #sidebar form #sidebar #files_list, #filelist .filedetails form, #filelist .filedetails ul, #logout {
	margin:0;
	padding:0;
}
#filelist tr.rowhover, a:hover, h1, #sidebar h2, #filelist th, #filelist tfoot td, #filelist .details span.hide, #filelist .hover {
	background:<?php echo COLOURONE;?>;
	color:<?php echo COLOURONETEXT;?>;
}
.error {color:red;}
.ok {color:<?php echo COLOURONE;?>;}
.hidden {display:none;}
a {
	color:<?php echo COLOURONE;?>;
	text-decoration:none;
}
a:hover {
	text-decoration:underline;
}
#logout {
	position:absolute;
	top:4px;
	right:4px;
	left:auto;
	bottom:auto;
}
h1 a, #logout a {
	color:<?php echo COLOURONETEXT;?>;
}
h1 {
	font-size:2em;
	font-weight:bold;
	padding:0.2em;
	margin-bottom:25px;
}
h2 {
	font-size:1.5em;
	font-weight:normal;
	margin-left:265px;
}
/* Sidebar */
#sidebar {
	width:225px;
	margin:0 40px 0 25px;
	float:left;
	font-size:10px;
}
#sidebar .section {
	background:<?php echo COLOURTWO;?>;
	margin:0 0 2.5em 0;
	padding-bottom:0.8em;
	border:1px solid black;
}
#sidebar .section form {
	padding:0.8em 0.8em 0 0.8em;
}
#sidebar h2 {
	font-size:1.2em;
	font-weight:bold;
	padding:0.4em 0 0.4em 0.4em;
	margin:0;
	border-bottom:1px solid black;
}
#sidebar h3 {
	font-weight:bold;
	font-size:1.2em;
	margin:1em 0 0.5em 0;
}
#sidebar ul {
	margin:0.8em 0 0 1.5em;
	padding:0;	
}
#sidebar #files_list {
	margin-left:1.5em;
}
#sidebar #uploadbutton {
	margin:1em 0 0 0;
}
#files_list span.error {
	cursor:pointer;
}
#files_list span.error:hover {
	text-decoration:underline;
}
#uploadsection input {
	width:200px;
}
#mkdir {
	width:140px;
}
#mkdirsubmit {
	width:40px;
}
#sidebar p {
	text-align:center;
}
/* Status box */
#status p {
	text-align:left;
	padding:0.5em 0.8em;
	font-size:14px;
}
#sidebar #status {
	background-color:<?php echo COLOURHIGHLIGHT;?>;
}
/* File list */
#filelist td a {
	color:<?php echo COLOURTEXT;?>;
	display:block;
	width:100%;
	height:100%;
	padding:0.2em 3.5em 0.2em 0.6em;
}
#filelist a:hover {
	background:inherit;
	text-decoration:none;
}
#filelist tr.rowhover a:hover {
	color:<?php echo COLOURONETEXT;?>;
}
#filelist {
	border:1px solid black;
	border-collapse:collapse;
	margin-left:25px;
}
#filelist tfoot td, #filelist th {
	border-top:1px solid black;
	border-bottom:1px solid black;
}
#filelist th, #filelist tfoot td {
	font-weight:bold;
}
#filelist th.size a {
	color:white;
}
#filelist th.size {
	text-align:right;
}
#filelist th {
	padding:0.3em 0.6em;
	text-align:left;
}
#filelist td.details {
	padding:0.3em 0;
}
#filelist td.size {
	text-align:right;
}
#filelist tfoot td {
	font-size:10px;
	text-align:right;
	font-weight:normal;
}
#filelist tr {
	background:#fff;
}
#filelist tr.odd {
	background:<?php echo COLOURTWO;?>;
}
#filelist tr.dir td.name {
	font-weight:bold;
}
#filelist tr.rowhover {
	background:<?php echo COLOURONE;?>;
	color:<?php echo COLOURONETEXT;?>;
}
#filelist .details span.show, #filelist .details span.hide {
	cursor:pointer;
	padding:4px 4px;
}
#filelist .hover, #filelist .filedetails ul li {
	cursor:pointer;
}
#filelist .filedetails {
	background:<?php echo COLOURHIGHLIGHT;?>;
	font-size:10px;
	border-top:2px solid black;
	border-bottom:2px solid black;
	padding:1em 0.5em;
}
#filelist .filedetails td {
	width:275px;
}
#filelist .filedetails .newvalue {
	width:150px;
}
#filelist .filedetails form {
	padding:0.3em;
}
#filelist .filedetails label {
	display:block;
	font-weight:bold;
	margin:0 0 0.5em 0;
}
#filelist .filedetails ul {
	list-style:none;
	padding:0.3em 1.2em 0.3em 0.3em;
	width:60px;
	float:left;
}
#filelist .filedetails ul li.selected {
	font-weight:bold;
}
#filelist td.error {
	padding:1em 3em;
}
/* Edit form */
form#edit {
	margin-left:265px;
}
form#edit textarea {
	margin:1.5em 0 1em 0;
}
/* Login box */
#loginbox {
	margin:25px;
}
#loginbox label {
	font-family:monospace;
}
/* Footer */
#footer {
	font-size:10px;
	clear:both;
	margin:25px;
}
.seperator {
	border-top:2px solid <?php echo COLOURONE;?>;
}
	</style>
<?php include ($ROOT."/includes/head.php") ?>
</head>
<body>
<div class="container">
<table cellspacing="0" cellpadding="0" align="center">
<!--HEADER START-->
  <tr>
    <td colspan="2" valign="top">
<?php include ($ROOT."/admin/includes/header.php") ?>
	</td>
  </tr>
  <!--HEADER END-->
  <!--LEFT COLUMN START-->
  <tr>
    <td class="leftcolumn" width="150px" valign="top">
<?php include ($ROOT."/includes/left.php") ?>
    </td>
	<!--LEFT COLUMN END-->
    <td valign="top">
	<!--PATHWAY START-->
<?php include ($ROOT."/includes/pathway.php") ?>
	<!--PATHWAY END-->
	<!--MAINBODY START-->
	<div class="content_outer">
	<div class="content_inner">
	<?php echo $str;?>
	</div>
	</div>
	</td>
  </tr>
  <!--MAINBODY END-->
  <!--FOOTER START-->
  <tr>
    <td colspan="2">
<?php include ($ROOT."/includes/footer.php") ?>
	</td>
  </tr>
  <!--FOOTER START-->
</table>
<?php include ($ROOT."/includes/foot.php"); ?>
</body>
</html>