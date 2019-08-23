<div class="row">
	<div class="col-md-12">
		<div class="well">
			<form class="form-horizontal" method="post" enctype="multipart/form-data">
				<input type="hidden" name="formid" value="frm">

				<div class="form-group [+userid.errorClass+][+name.requiredClass+]">
					<label for="userid" class="col-sm-4 control-label">* Кому файл?</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" id="userid" placeholder="Имя" name="userid" value="2[+userid.value+]">
						[+userid.error+]
					</div>
				</div>
				<div class="form-group [+first.errorClass+][+first.requiredClass+]">
					<label for="first" class="col-sm-4 control-label">* Приложите документ (Word или Pdf)</label>
					<div class="col-sm-8">
						<input type="file" class="form-control" id="first" name="first">
						[+first.error+]
					</div>
				</div>
				[+form.messages+]
				<div class="form-group">
					<div class="col-sm-offset-4 col-sm-8">
						<button type="submit" class="btn btn-default"><i class="glyphicon glyphicon-envelope"></i> Отправить</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>