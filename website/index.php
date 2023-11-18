<?php
require_once 'class/config.php';
require_once 'class/database.php';
require_once 'steamauth/steamauth.php';
require_once 'class/utils.php';


$db = new DataBase();
if (isset($_SESSION['steamid'])) {

	include('steamauth/userInfo.php');
	$steamid = $steamprofile['steamid'];

	$weapons = UtilsClass::getWeaponsFromArray();
	$skins = UtilsClass::skinsFromJson();
	$querySelected = $query3 = $db->select("SELECT `weapon_defindex`, `weapon_paint_id` FROM `wp_player_skins` WHERE `wp_player_skins`.`steamid` = :steamid", ["steamid" => $steamid]);
	$selectedSkins = UtilsClass::getSelectedSkins($querySelected);
	$queryResult = $db->select("SELECT * FROM `wp_player_knife` WHERE `wp_player_knife`.`steamid` = :steamid", ["steamid" => $steamid]);
    $selectedKnife = !empty($queryResult) ? $queryResult[0] : null;
	$knifes = UtilsClass::getKnifeTypes();

	if (isset($_POST['forma'])) {
		$ex = explode("-", $_POST['forma']);

		if ($ex[0] == "knife") {
			$db->query("INSERT INTO `wp_player_knife` (`steamid`, `knife`) VALUES(:steamid, :knife) ON DUPLICATE KEY UPDATE `knife` = :knife", ["steamid" => $steamid, "knife" => $knifes[$ex[1]]['weapon_name']]);
		} else {
			if (array_key_exists($ex[1], $skins[$ex[0]])) {
				if (array_key_exists($ex[0], $selectedSkins)) {
					$db->query("UPDATE wp_player_skins SET weapon_paint_id = :weapon_paint_id WHERE steamid = :steamid AND weapon_defindex = :weapon_defindex", ["steamid" => $steamid, "weapon_defindex" => $ex[0], "weapon_paint_id" => $ex[1]]);
				} else {
					$db->query("INSERT INTO wp_player_skins (`steamid`, `weapon_defindex`, `weapon_paint_id`) VALUES (:steamid, :weapon_defindex, :weapon_paint_id)", ["steamid" => $steamid, "weapon_defindex" => $ex[0], "weapon_paint_id" => $ex[1]]);
				}
			}
		}
		header("Location: index.php");
	}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<link rel="=stylesheet" href="bootstrap.min.css">
	<script src="bootstrap.bundle.min.js"></script>
	<link rel="stylesheet" href="style.css">
	<title>CS2 Simple Weapon Paints</title>
</head>

<body>

	<?php
	if (!isset($_SESSION['steamid'])) {
		echo "<div class='bg-primary'><h2>To choose weapon paints loadout, you need to ";
		loginbutton("rectangle");
		echo "</h2></div>";
	} else {
		echo "<div class='bg-primary'>Your current weapon skin loadout<form action='' method='get'><button class='btn btn-secondary' name='logout' type='submit'>Logout</button></form></div>";
		echo "<div class='card-group mt-2'>";
		?>

		<div class="col-sm-2">
			<div class="card text-center mb-3 border border-primary">
				<div class="card-body">
					<?php
					$actualKnife = $knifes[0];
					if ($selectedKnife !== null) {
						foreach ($knifes as $knife) {
							if ($selectedKnife['knife'] == $knife['weapon_name']) {
								$actualKnife = $knife;
								break;
							}
						}
					}
					echo "<div class='card-header'>";
					echo "<h6 class='card-title item-name'>Knife type</h6>";
					echo "<h5 class='card-title item-name'>{$actualKnife["paint_name"]}</h5>";
					echo "</div>";
					echo "<img src='{$actualKnife["image_url"]}' class='skin-image'>";
					?>
				</div>
				<div class="card-footer">
					<form action="" method="POST">
						<select name="forma" class="form-control select" onchange="this.form.submit()" class="SelectWeapon">
							<option>Select knife</option>
							<?php
							foreach ($knifes as $knifeKey => $knife) {
								if ($selectedKnife['knife'] == $knife['weapon_name'])
									echo "<option selected value=\"knife-{$knifeKey}\">{$knife['paint_name']}</option>";
								else
									echo "<option value=\"knife-{$knifeKey}\">{$knife['paint_name']}</option>";
							}
							?>
						</select>
					</form>
				</div>
			</div>
		</div>

		<?php
		foreach ($weapons as $defindex => $default) { ?>
			<div class="col-sm-2">
				<div class="card text-center mb-3">
					<div class="card-body">
						<?php
						if (array_key_exists($defindex, $selectedSkins)) {
							echo "<div class='card-header'>";
							echo "<h5 class='card-title item-name'>{$skins[$defindex][$selectedSkins[$defindex]]["paint_name"]}</h5>";
							echo "</div>";
							echo "<img src='{$skins[$defindex][$selectedSkins[$defindex]]['image_url']}' class='skin-image'>";
						} else {
							echo "<div class='card-header'>";
							echo "<h5 class='card-title item-name'>{$default["paint_name"]}</h5>";
							echo "</div>";
							echo "<img src='{$default["image_url"]}' class='skin-image'>";
						}
						?>
					</div>
					<div class="card-footer">
						<form action="" method="POST">
							<select name="forma" class="form-control select" onchange="this.form.submit()" class="SelectWeapon">
								<option>Select skin</option>
								<?php
								foreach ($skins[$defindex] as $paintKey => $paint) {
									if (array_key_exists($defindex, $selectedSkins) && $selectedSkins[$defindex] == $paintKey)
										echo "<option selected value=\"{$defindex}-{$paintKey}\">{$paint['paint_name']}</option>";
									else
										echo "<option value=\"{$defindex}-{$paintKey}\">{$paint['paint_name']}</option>";
								}
								?>
							</select>
						</form>
					</div>
				</div>
			</div>
		<?php } ?>
	<?php } ?>
	</div>

</body>

</html>
