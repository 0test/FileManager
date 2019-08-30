# FileManager
Файл-менеджер для веб-юзеров.

Веб-юзер может загружать и удалять файлы, назначать, кто из других юзеров может их видеть. 

Файлы отдаются через php с проверкой доступа, т.е. посторонний человек не сможет скачать или посмотреть не предназначенный для него файл.

- альфа!

# Установка #

Создать таблицу
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
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

```
Скопировать файлы в папку `assets/snippets/FileManager/`

Создать папку для загрузки файлов, закрыть доступ к ней с помощью `.htaccess`.

## Пример ##
```
[!fileManager? 
&mode='admin'
&users_group='опт'
&upload_path= 'assets/files/userfiles/'
&noneTPL= '@CODE: files not found'
!]
```
## Параметры ##
Обязательных параметров нет, всё работает из коробки.

### &TemplatePath ###

По-умолчанию `assets/snippets/fileManager/tpl/`

Путь для шаблонов. 

### &TemplateExtension ###

По-умолчанию `tpl`

Расширение файлов шаблонов.

**Совет:**
Переопределив эти переменные, вы можете сделать свои шаблоны для формы загрузки, списка файлов и системных сообщений. 

Зачастую достаточно сменить только `TemplatePath` и скопировать туда имеющиеся шаблоны. `TemplateExtension` менять при этом не нужно.
Шаблонизация обычная для Evolution - если вы создали для шаблона файл, то укажите `@FILE: filename`. (расширение будет подставлено автоматически!) Если это чанк в админке, то `chunk`. Если вы пишете инлайн-шаблон, то `@CODE: text`.


## &mode ##

Режим работы. 

При значении `user` показывает пользователю доступные для скачивания файлы. 

При значении `admin` также показывает менеджер для загрузки новых файлов.

## &users_group ##

По-умолчанию покажет список всех веб-пользователей сайта. 

Группа/группы пользователей, которых можно будет выбирать в поле видимости файла. 

Пример: `&users_group='опт,розница'`

## &upload_path ##

По-умолчанию `assets/files/userfiles/` 

Путь для загрузки файлов.

Внутри будет создана папка `uid_Х` где `Х` это id юзера.

## &noneTPL ##

Шаблон сообщения о том, что файлы не найдены.

## &formValidateRules ##

Правила для валидации полей формы. 

По-умолчанию:
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

## &formFileRules ##
	
Правила для валидации файлов. 

По-умолчанию:
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








