<div class="ttvpage-sub-title-wrapper">
			<h4 class="ttvpage-sub-title">Новый файл</h4>
</div>
<div class="row">
	<div class="ttv-sub-pages-inner-content">
		<div class="col-md-12">
			<div class="well">
				<form class="form-horizontal" method="post" enctype="multipart/form-data">
					<input type="hidden" name="formid" value="frm">

					<div class="form-group [+userid.errorClass+][+name.requiredClass+]">
						<label for="userid" class="col-sm-4 control-label">* Кому файл?</label>
						<div class="col-sm-8">
							<select name="userid" id="userid" class="form-control">
							  [+users_list+]
							</select>
							[+userid.error+]
						</div>
						
					</div>
					
					<div class="form-group [+files_group_name.errorClass+][+files_group_name.requiredClass+]">
						<label for="files_group_name" class="col-sm-4 control-label">* Тема:</label>
						<div class="col-sm-8">
							<input type="text" list="themes" class="form-control" id="files_group_name" placeholder="Куда включить файл? Выберите или напишите новую тему." name="files_group_name" value="[+files_group_name.value+]">
							[+files_group_name.error+]
								<datalist id="themes">
									[+datalist+]
								</datalist> 

						</div>
						
					</div>
					
					
					<div class="form-group [+file_description.errorClass+][+file_description.requiredClass+]">
						<label for="file_description" class="col-sm-4 control-label">* Описание</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="file_description" placeholder="Что это за файл?" name="file_description" value="[+file_description.value+]">
							[+file_description.error+]
						</div>
					</div>
					
					<div class="form-group [+first.errorClass+][+first.requiredClass+]">
						<label for="first" class="col-sm-4 control-label">* Приложите документ</label>
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
</div>