<?php
//подключаем библиотеку
include_once '../xmlrpc.inc';

$GLOBALS['xmlrpc_internalencoding'] = 'UTF-8';
header('Content-type:text/html;charset=utf-8');

//логин и пароль
$u_name = 'ddeath123';
$u_pass = 'qwe123';
 
//обращаемся к test.wordpress.loc/xmlrpc.php
$wp = new xmlrpc_client('/xmlrpc.php','ddeath123.wordpress.com',80);
//кодировка клиента
$wp->request_charset_encoding = 'UTF-8';
//чтоб возвращал в виде php-переменных
$wp->return_type = 'phpvals';
 
$struct = array();
//тип записи: page - страница, post - пост
$struct['post_type'] = new xmlrpcval('post', 'string');
//наименование статьи
$struct['title'] = new xmlrpcval('Заголовок статьи', 'string');
//сама статья отформатированная в html
$struct['description'] = new xmlrpcval(
'В черном-черном городе, на черной-черной улице, '.
'в черном-черном доме,в черно-черной квартире сидит '.
'черный-черный мужик и говорит:
- Никогда больше '.
'не буду <strong>сам заправлять картриджи!</strong>', 'string');
//краткое описание, которое будет отображено на главной
$struct['mt_excerpt'] = new xmlrpcval(
'Краткое описание статьи', 'string');
//массив с тегами
$struct['mt_keywords'] = new xmlrpcval(array(
new xmlrpcval('анекдот', 'string'),
new xmlrpcval('юмор', 'string')), 'array');
//комментарии: 0 - запрещены, 1 - разрешены
$struct['mt_allow_comments'] = new xmlrpcval(1, 'int');
//пинги: 0 - запрещены, 1 - разрешены
$struct['mt_allow_pings'] = new xmlrpcval(1, 'int');
//вместо таймштампа можно передать
//объект класса DateTime/*
/*$struct['dateCreated'] = new xmlrpcval(
time(), 'dateTime.iso8601');*/
//статуст записи: publish - публичная, private - приватная
$struct['post_status'] = new xmlrpcval('publish', 'string');
//массив с категориями: все категории должны существовать
$struct['categories'] = new xmlrpcval(array(
new xmlrpcval('Uncategorized',
'string')), 'array');
 
//собираем все в кучу
$params = array( //ид блога
new xmlrpcval(0, 'int'), //логин
new xmlrpcval($u_name, 'string'), //пароль
new xmlrpcval($u_pass, 'string'), //данные
new xmlrpcval($struct, 'struct'),
//публикация: true - опубликована,
//false - не опубликована
new xmlrpcval(true, 'boolean'));
 
//вызываем процедуру metaWeblog.newPost
$r = $wp->send(new xmlrpcmsg('metaWeblog.newPost', $params));
//если ошибка, сообщаем об ошибке постинга
if ($r->faultCode()) {
die('Ошибка постинга:' . $r->faultString());
}
//WP вернет идентификатор поста в случае успеха
$p = $r->value();
 
echo 'Запостили пост успешно. Его идентификатор '.
'имеет номер ' . $p .'. Прочитать статью можно'.
' <a href="http://test.wordpress.loc/?p=' . $p .     '">здесь</a> ';