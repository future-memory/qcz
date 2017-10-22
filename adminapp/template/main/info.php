<?php include(APP_ROOT . '/template/common/header.php'); ?>
<style type="text/css">
	.panel {
		border: none;
	}
	.table>tbody>tr>td {
		border-top: none;
	}
</style>
<div class="container">
	<p></p>
	<div class="row">
		<div class="panel panel-default">
		  <div class="panel-heading">
		    <h3 class="panel-title">在线成员</h3>
		  </div>
		  	<div class="panel-body">
		  		<table class="table">
		  			<tbody>
		  				<tr>
				  		<?php
				  			foreach ($data['onlines'] as $key => $member) {
				  				echo '<td><a href="#">' . $member['username'] .'</a></td>';
				  			}
				  		?>	  						
		  				</tr>
		  			</tbody>
		  		</table>
		  	</div>
		</div>
	</div>
</div>
<?php include(APP_ROOT . '/template/common/footer.php'); ?>

    </body>
</html>