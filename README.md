# FileSystem

[![Build Status](https://travis-ci.org/SunriseDigital/FileSystem.svg?branch=master)](https://travis-ci.org/SunriseDigital/FileSystem)


[あの評判の悪い本](http://www.amazon.co.jp/%E9%96%A2%E6%95%B0%E5%9E%8B%E3%83%97%E3%83%AD%E3%82%B0%E3%83%A9%E3%83%9F%E3%83%B3%E3%82%B0%E3%81%AB%E7%9B%AE%E8%A6%9A%E3%82%81%E3%81%9F-IQ145%E3%81%AE%E5%A5%B3%E5%AD%90%E9%AB%98%E6%A0%A1%E7%94%9F%E3%81%AE%E5%85%88%E8%BC%A9%E3%81%8B%E3%82%89%E5%8F%97%E3%81%91%E3%81%9F%E7%89%B9%E8%A8%935%E6%97%A5%E9%96%93-%E5%B2%A1%E9%83%A8-%E5%81%A5/dp/4798043761)に触発されて関数プログラミングチックなファイルを集めるクラスを作成してみました。

といっても、下記の特徴を持ってるだけですが・・・

* 高階関数で操作する。
* Immutableである。

## サンプルコード

```php
//ただ単にファイル名を表示するmap関数
function display($template_file){
  echo $template_file.PHP_EOL;
}

//特定のディレクトリを集めておく
$area_dirs = FileSystem::create('/')
  ->children('FileSystem::hasChild', 'foo')
  ->children('FileSystem::nameIs', 'bar');

//immutableなので繰り返し使えます。
$area_dirs
  ->children('FileSystem::nameIs', 'user.config.tpl')
  ->map('display');

$area_dirs
  ->children('FileSystem::nameMatch', '@^sp|www2?$@')
  ->children('FileSystem::nameIs', 'Web.config.tpl')
  ->map('display');
```

## リファレンス

### 高階関数

| method | 説明 |
| ------ | ---- |
| children | 直下のエントリーにcallbackでフィルタリングし取得します。 |
| filter | 自分自身（現在のエントリー）をフィルタリングします。 |
| recursive | 自分より下位の全てのファイルをフィルタリングし取得します。 |
| map | 自分自身（現在のエントリー）に対してmap処理をします。 |



各高階関数は可変引数を取ります。最初の引数はコールバック関数、それ以降の引数はコールバックに渡されます。また、コールバック関数の最初の引数は必ずファイル名あるいはディレクトリ名なので2番目以降に、高階関数に渡した2番目以降の引数が渡されることになります。

例えば

```php
FileSystem::create('/')->children('someCallBack', 'foo', 'bar');
```

では下記のように`callable`を呼びます。

```php
someCallBack($file_path, 'foo', 'bar');
```

コールバック関数はBooleanを返してください。

```php
function someCallBack($file_path, $arg1, $arg2){
  return true;
}
```

コールバックは`is_callable`が`true`になればなんでもOKです。当たり前ですが`FileSystem`のメンバー関数である必要はありません。


### ユーティリティー関数

よく使うであろうフィルター関数をいくつか作りました。

| method | 説明 |
| ------ | ---- |
| nameIs | ファイル名によるフィルタ |
| hasChild | 直下に特定の名前のエントリーがあるディレクトリを取得 |
| nameMatch | 正規表現によるフィルター |



