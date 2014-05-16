<? include 'components/common.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<? include 'components/admin/head.php'; ?>
</head>
<body class="open-eyes">
	<div class="container main" role="main">

		<? include 'components/header-logged-in.php'; ?>

		<div class="container content">
			<h1 class="badge">Edit profile</h1>

			<div class="box content">
				<div class="row">
					<? include 'components/admin/sidebar.php'; ?>
					<? include 'components/admin/user-edit.php'; ?>
				</div>
			</div>
		</div>
		<? include 'components/footer.php'; ?>
	</div>
</body>
</html>