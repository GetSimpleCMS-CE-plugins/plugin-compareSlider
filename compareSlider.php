<?php
/*
Plugin Name: CompareSlider
Description: A plugin to create before-and-after image comparison sliders
Version: 1.0
Author: GetSimple CE
Author URI: https://www.getsimple-ce.ovh
*/

# Get correct ID for plugin
$thisfile = basename(__FILE__, ".php");

# Register plugin
register_plugin(
	$thisfile,                // Plugin ID
	'CompareSlider',          // Plugin name
	'1.0',                    // Plugin version
	'Multicolor',           // Plugin author
	'https://ko-fi.com/multicolorplugins', // Author website
	'A plugin for creating image comparison sliders (sqlite3 required)', // Plugin description
	'compareSlider',          // Page type (admin tab)
	'compareSliderAdmin'      // Main function (administration)
);

# Activate hooks
add_action('theme-header', 'cssCompareSlider');
add_action('theme-header', 'headerShortcode');
add_action('theme-footer', 'scriptCompareSlider');
add_action('header', 'w3css');
add_action('pages-sidebar', 'createSideMenu', [$thisfile, 'Compare Slider <svg xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle" width="1.2em" height="1.2em" viewBox="0 0 24 24"><rect width="24" height="24" fill="none"/><path fill="none" stroke="#483D8B" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h7m4-16h1a2 2 0 0 1 2 2v1m0 10v1a2 2 0 0 1-2 2h-1m3-9v2M12 2v20"/></svg>', 'comparesliderlist']);
add_action('compareSlider-sidebar', 'createSideMenu', [$thisfile, 'New Compare Slider <svg xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle" width="1.2em" height="1.2em" viewBox="0 0 24 24"><rect width="24" height="24" fill="none"/><path fill="none" stroke="#483D8B" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h7m4-16h1a2 2 0 0 1 2 2v1m0 10v1a2 2 0 0 1-2 2h-1m3-9v2M12 2v20"/></svg>', 'createnewcompareslider']);

# Functions for including CSS and JS
function w3css()
{
	global $SITEURL;
	echo '<link rel="stylesheet" href="' . $SITEURL . 'plugins/compareSlider/css/w3.css"/>';
}

function cssCompareSlider()
{
	global $SITEURL;
	echo '<link rel="stylesheet" href="' . $SITEURL . 'plugins/compareSlider/css/img-comparison-slider.css"/>';
}

function scriptCompareSlider()
{
	global $SITEURL;
	echo '<script src="' . $SITEURL . 'plugins/compareSlider/js/img-comparison-slider.js"></script>';
}

# Database setup
function compareSliderMakeDB()
{
	try {
		$db = new SQLite3(GSDATAOTHERPATH . 'compareSliderDB.db');
		$db->exec('
            CREATE TABLE IF NOT EXISTS elements (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                beforeIMG TEXT NOT NULL,
                afterIMG TEXT NOT NULL
            )
        ');
	} catch (Exception $e) {
		echo "Error: " . $e->getMessage();
	}
}

# Backend: Admin interface
function compareSliderAdmin()
{
	global $SITEURL, $GSADMIN;

	compareSliderMakeDB();

	# Display comparison slider list
	if (isset($_GET['comparesliderlist'])) { ?>
		<div class="w3-parent">
			<h3><strong>Compare Slider </strong> <svg xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle" width="1.2em" height="1.2em" viewBox="0 0 24 24"><rect width="24" height="24" fill="none"/><path fill="none" stroke="#483D8B" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h7m4-16h1a2 2 0 0 1 2 2v1m0 10v1a2 2 0 0 1-2 2h-1m3-9v2M12 2v20"/></svg></h3>
			<p>A plugin for creating image comparison sliders (sqlite3 required)</p>
			<hr>
			<table>
				<tr>
					<th style="text-align:center;">ID</th>
					<th style="text-align:center;">Title</th>
					<th style="text-align:center;">Before Image</th>
					<th style="text-align:center;">After Image</th>
					<th style="text-align:center;">Edit/Delete</th>
				</tr>
				<?php
				$db = new SQLite3(GSDATAOTHERPATH . 'compareSliderDB.db');
				$result = $db->query('SELECT * FROM elements');
				while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
					echo "
                        <tr>
                            <td style='text-align:center;'>
                              <span class='copier cke' data-copy='[% cs={$row['id']} %]'>  [% cs={$row['id']} %]</span><br> 
							  <span class='copier tpl' data-copy='&lt;? compareSlider({$row['id']}) ?>'>&lt;? compareSlider({$row['id']}) ?></span>
                            </td>
                            <td style='text-align:center;'>{$row['title']}</td>
                            <td style='text-align:center;'><img src='{$SITEURL}{$row['beforeIMG']}' style='width:50px;height:50px;object-fit:cover;'></td>
                            <td style='text-align:center;'><img src='{$SITEURL}{$row['afterIMG']}' style='width:50px;height:50px;object-fit:cover;'></td>
                            <td style='text-align:center;'>
                                <a class='w3-button w3-tiny w3-orange w3-round' style='text-decoration:none' href='?id=compareSlider&edit={$row['id']}'>Edit</a>
								
                                <a class='w3-button w3-tiny w3-red w3-round delete-link' style='text-decoration:none' href='?id=compareSlider&comparesliderlist&delete={$row['id']}'>Delete</a>
                            </td>
                        </tr>";
				}
				$db->close();
				?>
			</table>

			<script>
				document.querySelectorAll(".copier").forEach(element => {
					element.addEventListener('click', () => {
						const copyText = element.getAttribute('data-copy');
						navigator.clipboard.writeText(copyText)
							.then(() => {
								alert("Copied the shortcode: " + copyText);
							})
						 
					});
				});
			</script>
			<script>
				document.querySelectorAll('.delete-link').forEach(link => {
					link.addEventListener('click', function(event) {
						const confirmed = confirm('Are you sure you want to delete this item?');
						if (!confirmed) {
							event.preventDefault(); // Cancel the navigation
						}
					});
				});
			</script>

			<hr style="margin-top:30px">
			<div id="paypal" style="padding-top:10px">
				<a href='https://ko-fi.com/I3I2RHQZS' target='_blank'>
					<img height='36' style='border:0px;height:36px;margin-top:30px;'
						src='https://storage.ko-fi.com/cdn/kofi5.png?v=6'
						border='0' alt='Buy Me a Coffee at ko-fi.com' />
				</a>
			</div>
		</div>
		<?php

		# Handle delete action
		if (isset($_GET['delete'])) {
			$db = new SQLite3(GSDATAOTHERPATH . 'compareSliderDB.db');
			$stmt = $db->prepare('DELETE FROM elements WHERE id = :id');
			$stmt->bindValue(':id', (int)$_GET['delete'], SQLITE3_INTEGER);
			$stmt->execute();
			$db->close();

			$redirect_url = $SITEURL . $GSADMIN . '/load.php?id=compareSlider&comparesliderlist';
			echo "<meta http-equiv='refresh' content='0;url=$redirect_url'>";
			exit;
		}

		# Display create/edit form
	} elseif (isset($_GET['createnewcompareslider']) || isset($_GET['edit'])) {
		$row = [];
		if (isset($_GET['edit'])) {
			$db = new SQLite3(GSDATAOTHERPATH . 'compareSliderDB.db');
			$stmt = $db->prepare('SELECT * FROM elements WHERE id = :id');
			$stmt->bindValue(':id', (int)$_GET['edit'], SQLITE3_INTEGER);
			$result = $stmt->execute();
			$row = $result->fetchArray(SQLITE3_ASSOC);
			$db->close();
		} ?>
		<form method="post">
			<h3>Add/Edit Compare Slider <svg xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle" width="1.2em" height="1.2em" viewBox="0 0 24 24"><rect width="24" height="24" fill="none"/><path fill="none" stroke="#483D8B" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h7m4-16h1a2 2 0 0 1 2 2v1m0 10v1a2 2 0 0 1-2 2h-1m3-9v2M12 2v20"/></svg></h3>
			<hr style="margin-bottom:30px;">
			
			<label style="margin-top:20px">Slider Title</label>
			<input type="text" name="slider_title" value="<?php echo @$row['title']; ?>" class="slider_title w3-input w3-border w3-margin-bottom" onkeydown="if(event.keyCode==13) return false;">
			<hr style="border: 1px solid #ccc;margin-top:30px">
			
			<?php if (!empty($row['beforeIMG'])): ?>
				<img src="<?php echo $SITEURL . $row['beforeIMG']; ?>" class="w3-border" style="width:70px;height:70px;object-fit:cover">
			<?php endif; ?>
			<label style="margin-top:20px">Before Photo</label>
			<input type="text" name="before_photo" value="<?php echo @$row['beforeIMG']; ?>" class="before_photo w3-input w3-border w3-margin-bottom">
			
			<button class="addPhotoBefore addPhoto w3-button w3-medium w3-blue w3-round">Add Photo</button>
			<script>
				document.querySelector(".addPhotoBefore").addEventListener("click", (e) => {
					e.preventDefault();
					window.open("<?php echo $SITEURL; ?>plugins/compareSlider/filebrowser/imagebrowser.php?type=images&CKEditor=post-content&input=before_photo",
						"", "left=10,top=10,width=960,height=500");
				});
			</script>
			
			<hr style="border: 1px solid #ccc;margin-top:30px">
			
			<?php if (!empty($row['afterIMG'])): ?>
				<img src="<?php echo $SITEURL . $row['afterIMG']; ?>"
					class="w3-border" style="width:70px;height:70px;object-fit:cover">
			<?php endif; ?>
			<label style="margin-top:20px">After Photo</label>
			<input type="text" name="after_photo" value="<?php echo @$row['afterIMG']; ?>"
				class="after_photo w3-input w3-border w3-margin-bottom">
			<button class="addPhotoAfter addPhoto w3-button w3-medium w3-blue w3-round">Add Photo</button>
			<script>
				document.querySelector(".addPhotoAfter").addEventListener("click", (e) => {
					e.preventDefault();
					window.open("<?php echo $SITEURL; ?>plugins/compareSlider/filebrowser/imagebrowser.php?type=images&CKEditor=post-content&input=after_photo",
						"", "left=10,top=10,width=960,height=500");
				});
			</script>
			
			<hr style="border: 1px solid #ccc;margin-top:30px">
			
			<div class="w3-margin-top w3-center"><button class="savecompare w3-button w3-medium w3-green w3-round" name="submit">Save</button></div>
			<hr style="margin-top:50px">
			<div id="paypal" style="padding-top:10px">
				<a href='https://ko-fi.com/I3I2RHQZS' target='_blank'>
					<img height='36' style='border:0px;height:36px;margin-top:30px;'
						src='https://storage.ko-fi.com/cdn/kofi5.png?v=6'
						border='0' alt='Buy Me a Coffee at ko-fi.com' />
				</a>
			</div>
		</form>
<?php

		# Handle form submission
		if (isset($_POST['submit'])) {
			compareSliderMakeDB();
			$db = new SQLite3(GSDATAOTHERPATH . 'compareSliderDB.db');

			if (isset($_GET['edit']) && !empty($_GET['edit'])) {
				$stmt = $db->prepare('UPDATE elements SET title = :title, beforeIMG = :beforeIMG, afterIMG = :afterIMG WHERE id = :id');
				$stmt->bindValue(':title', $_POST['slider_title'], SQLITE3_TEXT);
				$stmt->bindValue(':beforeIMG', $_POST['before_photo'], SQLITE3_TEXT);
				$stmt->bindValue(':afterIMG', $_POST['after_photo'], SQLITE3_TEXT);
				$stmt->bindValue(':id', (int)$_GET['edit'], SQLITE3_INTEGER);
			} else {
				$stmt = $db->prepare('INSERT INTO elements (title, beforeIMG, afterIMG) VALUES (:title, :beforeIMG, :afterIMG)');
				$stmt->bindValue(':title', $_POST['slider_title'], SQLITE3_TEXT);
				$stmt->bindValue(':beforeIMG', $_POST['before_photo'], SQLITE3_TEXT);
				$stmt->bindValue(':afterIMG', $_POST['after_photo'], SQLITE3_TEXT);
			}
			$stmt->execute();
			$db->close();

			$redirect_url = $SITEURL . $GSADMIN . '/load.php?id=compareSlider&comparesliderlist';
			echo "<meta http-equiv='refresh' content='0;url=$redirect_url'>";
			exit;
		}
	}
}

# Frontend: Shortcode and function for rendering sliders
function headerShortcode()
{
	function compareSliderShortcode($id)
	{ 
		$db = new SQLite3(GSDATAOTHERPATH . 'compareSliderDB.db');
		$stmt = $db->prepare('SELECT * FROM elements WHERE id = :id');
		$stmt->bindValue(':id', (int)$id[1], SQLITE3_INTEGER);
		$result = $stmt->execute();
		$row = $result->fetchArray(SQLITE3_ASSOC);
		$db->close();

		return '
            <style>
                img[slot="first"], img[slot="second"] {
                    margin-bottom: 0;
                }
            </style>
            <img-comparison-slider class="img-comparison-slider img-comparison-slider-' . $row['id'] . '">
                <img slot="first" alt="before - ' . $row['title'] . '" src="' . $row['beforeIMG'] . '" />
                <img slot="second" alt="after - ' . $row['title'] . '" src="' . $row['afterIMG'] . '" />
                <svg slot="handle" class="custom-animated-handle" xmlns="http://www.w3.org/2000/svg" 
                     width="100" viewBox="-8 -3 16 6">
                    <path stroke="#000" stroke-width="1.25" 
                          d="M -5 -2 L -7 0 L -5 2 M -5 -2 L -5 2 M 5 -2 L 7 0 L 5 2 M 5 -2 L 5 2" 
                          stroke-width="1" fill="#fff" vector-effect="non-scaling-stroke"></path>
                </svg>
            </img-comparison-slider>';
	}

	global $content;
	$content = preg_replace_callback(
		'/\[% cs=(.*) %\]/i',
		'compareSliderShortcode',
		$content
	);
}

function compareSlider($id)
{
	$db = new SQLite3(GSDATAOTHERPATH . 'compareSliderDB.db');
	$stmt = $db->prepare('SELECT * FROM elements WHERE id = :id');
	$stmt->bindValue(':id', (int)$id, SQLITE3_INTEGER);
	$result = $stmt->execute();
	$row = $result->fetchArray(SQLITE3_ASSOC);
	$db->close();

	echo '
        <style>
            img[slot="first"], img[slot="second"] {
                margin-bottom: 0;
            }
        </style>
        <img-comparison-slider class="img-comparison-slider img-comparison-slider-' . $row['id'] . '">
            <img slot="first" alt="before - ' . $row['title'] . '" src="' . $row['beforeIMG'] . '" />
            <img slot="second" alt="after - ' . $row['title'] . '" src="' . $row['afterIMG'] . '" />
            <svg slot="handle" class="custom-animated-handle" xmlns="http://www.w3.org/2000/svg" 
                 width="100" viewBox="-8 -3 16 6">
                <path stroke="#000" stroke-width="1.25" 
                      d="M -5 -2 L -7 0 L -5 2 M -5 -2 L -5 2 M 5 -2 L 7 0 L 5 2 M 5 -2 L 5 2" 
                      stroke-width="1" fill="#fff" vector-effect="non-scaling-stroke"></path>
            </svg>
        </img-comparison-slider>';
}
