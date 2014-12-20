var ormLang = {qtip_object_rev_control:"Использовать версионный контроль данных: позволяет отслеживать версии документов и изменения, влияет на шаблон автогенерации, добавляет дополнительные системные поля в таблицу базы данных.",qtip_object_save_history:"Хранить историю изменений данных: позволяет узнать автора, время и тип изменения.",qtip_object_name:"Имя объекта на латинице.<br>Обратите внимание, такое же имя будет и у модели, работающей с данными этого объекта. <br>Именование объектов происходит по таким же правилам, как и именование классов. Символ "_" означает вложенность директорий. <br>При именовании объектов необходимо использовать нижний регистр и латинский алфавит. <br>Имя будет использоваться в коде для вызова различных методов и создания объектов.<br>Например:<br>$news = new Db_Object(‘News’);<br>$newsModel = Model::factory(‘News’);<br>    ",qtip_object_title:"Заголовок объекта, любое текстовое название объекта, например “Новости”. Для разных локализаций используются различные хранилища заголовков. Таким образом, после создания объекта можно переключить локализацию платформы и заполнить заголовки на нужном языке, изменения сохранятся и не затрут заголовки на другой локали платформы. При отсутствии заголовков для текущей локализации в качестве  title будет сохранено значение поля name.",qtip_object_table:"Имя таблицы БД содержащей данные объекта (заполняется без префикса, если установлены настройки автоматического использование префиксов).",qtip_object_engine:"Тип хранилища таблицы БД: отличаются особенностями хранения данных и возможностями (более подробно стипами хранилищ можно ознакомиться в официальной документации  MySQL / MariaDB / Percona Server).",qtip_object_disable_keys:"Отключить поддержку внешних ключей: дополнительная опция, запрещает использование внешних ключейдля таблицы данных объекта, имеет больший приоритет над настройкой в configs/main.php.",qtip_object_link_title:"Поле, используемое в качестве заголовка объекта для внешней ссылки: используется в различных компонентах дизайнера интерфейсов, например, выводится в форме редактирования данных.",qtip_object_connection:"Подключение к базе данных: объекты ORM могут использовать различные независимые подключения к БД (базы данных могут находиться на разных серверах).",qtip_object_primary_key:"Первичный ключ: имя колонки первичного ключа, обязательно целочисленное автоинкрементальное поле,желательно именовать  id.",qtip_object_readonly:"Режим только чтение: запрещает запись в таблицу базы данных.",qtip_object_locked:"Режим блокировки изменений: запрещено изменение структуры таблицы базы данных (полезно для внешних баз данных, структуру которых запрещено изменять).",qtip_object_use_db_prefix:"Использовать префикс для имени таблицы БД.",qtip_field_dictionary:"Словарь, на который ссылается поле.",qtip_field_db_default:"Значение по умолчанию.",qtip_field_db_len:"Длина строки для строковых типов.<br>Обратите внимание, для целочисленных типов это количество отводимых под число символов для дополнения пробелами слева, при выводе значений меньших, чем ширина заданного столбца (в консоли).",qtip_field_db_scale:"Длина строкового представления вещественного числа, например, для 10,01 будет 4 (особенность типов полей  БД).",qtip_field_db_precision:"Длина строкового представления дробной части вещественного числа (для 0,01 будет 2).",qtip_field_db_type:"Тип поля в таблице БД: подробнее с типами полей можно ознакомиться в официальной документации  MySQL / MariaDB / Percona Server.",qtip_field_validator:"Дополнительный валидатор данных: представляет класс, реализующий интерфейс Validator_Interface и расположенный в  library/Validator.",qtip_field_link_type:"Тип ссылочного поля (ссылка на объект / ссылка на список объектов / ссылка на словарь).",qtip_field_object:"Объект, на который ссылается поле.",qtip_field_db_isNull:"Поле может принимать значение NULL.",qtip_field_required:"Поле обязательно к заполнению.",qtip_field_db_unsigned:"Поле беззнаковое (положительное).",qtip_field_allow_html:"Разрешить использовать html-теги в качестве значения: если разрешено, то автогенератор интерфейса будет использовать расширенный редактор текста.",qtip_field_is_search:"Поисковое поле: включается в поисковый запрос модели (LIKE %text%) и используется автогенератором интерфейсов.",qtip_field_unique:"Строковое поле. Уникальный индекс поля, используется валидатором ORM при сохранении данных, независимо от наличия уникального индекса таблицы БД. Если указать один и тот же индекс в нескольких полях, валидатор  ORM  будет использовать многоколоночный уникальный индекс",qtip_field_name:"Имя поля (латиница).",qtip_field_title:"Заголовок поля: любой текст описывающий поле, используется автогенератором в качестве лейбла, так же как и имя объекта может быть указано для каждой локализации.",qtip_field_type:"Тип поля",qtip_object_set_default:"Установить значение по умолчанию",qtip_object_use_acl:"Использовать список контроля доступа",qtip_object_acl:"Даптрер списка контроля доступа"};