				</div>
			</div>
		</div>

		<script>
			window.addEvent("domready", function() {
				$("jsn-upgrade-cancel").addEvent("click", function(e) {
					/* Send AJAX request to clear session */
					var jsonRequest = new Request.JSON({
						url: "<?php echo JURI::base() ; ?>" + "index.php?template=" + "<?php echo $this->template; ?>" + "&tmpl=jsn_upgrade&task=ajax_destroy_sesison&template_style_id=" + "<?php echo $templateStyleId; ?>" + "&rand=" + Math.random(), 
						onSuccess: function(jsonObj)
						{
							if (jsonObj.sessionclear)
							{
								window.top.SqueezeBox.close();
							}
						}
					}).post();
		   		});
			});
		</script>
	</body>
</html>