# FileManager
Файл-менеджер для веб-юзеров

- альфа!

## Пример ##
```
[!fileManager? 
&mode='admin'
&users_group='опт'
&upload_path= 'assets/files/userfiles/'
&noneTPL= '@CODE: files not found'
!]
```
## Парметры ##
* TemplatePath - путь для шаблонов. По-умолчанию `assets/snippets/fileManager/tpl/`
* TemplateExtension - расширение файлов щаблонов. По-умолчанию `tpl`
Переопределив эти переменные, вы можете сделать свои шаблоны для формы загрузки, списка файлов и системных сообщений. Зачастую достаточно сменить только `TemplatePath` и скопировать туда имеющиеся шаблоны. `TemplateExtension` менять при этом не нужно.
Шаблонизация в стиле DocLister. Т.е. если вы создали для шаблона файл, то укажите `@FILE: filename`. (Без `.tpl`, расширение будет подставлено автоматически!) Если это чанк в админке, то `chunk`. Если вы пишете инлайн-шаблон, то `@CODE: text`.
* mode - режим работы. При значении `user` показывает пользователю доступные для скачивания файлы. При значении `admin` также показывает менеджер для загрузки новых файлов.
* users_group - список групп пользователей. Этих пользователей можно будет выбирать в поле видимости файла. По-умолчанию покажет всех веб-пользователей сайта. Пример: `&users_group='опт,розница'`
* upload_path - куда грузить файлы. По-умолчанию `assets/files/userfiles/` Внутри будет создана папка `uid_Х` где `Х` это id юзера.
* noneTPL - шаблон сообщения о том, что файлы не найдены
* formValidateRules - правила для валидации полей формы. По-умолчанию:
```
{
	"userid":{
		"required":"Обязательно введите имя",
		"numeric":{
			"message":"Тут должна быть цифра"
		}
	},
	"file_description":{
		"required":"Обязательно заполните описание",
		"minLength":{
			"params":5,
			"message":"Должно быть не менее 5 символов"
		}
	}
}
```
*formFileRules - правила для валидации файлов. По-умолчанию:
```
{
	"first":{
		"required":"Приложите документ",
		"allowed":{
			"params": [ ["doc","docx", "jpg", "jpeg", "png", "gif", "pdf", "xls", "xlsx", "txt", "rtf", "zip", "rar"] ],
			"message": "Разрешены документы Word, Excel, картинки и архивы"
		},
		"maxSize" : {
			"params": 10000,
			"message": "Размер файла не должен превышать 10000 кб"
		}
	}
}
```








	
```
CREATE TABLE `{PREFIX}userfiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `user_filename` text NOT NULL,
  `real_filename` text NOT NULL,
  `file_description` text NOT NULL,
  `viewer_id` int(11) NOT NULL,
  `upload_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `files_group_name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

```
