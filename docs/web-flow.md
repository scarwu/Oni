# Web APP 運作流程

## 範圍

描述從 HTTP 請求進入入口腳本到回應輸出的完整流程，以及靜態檔案、快取、Controller 三條分流路徑的決策邏輯。

---

## 1. 初始化

```
index.php（Web Server 指向此入口）
  │
  ├─ new Oni\Web\App()
  │    ├─ Req::init()    ← 封裝 $_SERVER / $_GET / $_POST / $_FILES，建立 singleton
  │    └─ Res::init()    ← 回應輸出工具，建立 singleton
  │
  ├─ $app->setAttr('controller/namespace', ...)
  ├─ $app->setAttr('controller/path', ...)
  ├─ $app->setAttr('view/paths', [...])
  ├─ $app->setAttr('static/paths', [...])
  ├─ $app->setAttr('cache/path', ...)
  └─ $app->run()
```

## 2. `run()` 主流程

```
run()
  │
  ├─ up()
  │    ├─ 驗證 controller/namespace、controller/path 皆為字串
  │    │    └─ 否 → throw Exception('oni:exception:namespaceOrPathNotSet')
  │    ├─ Loader::append(namespace, path)   ← 註冊 autoload
  │    └─ 若 router/event/up 可呼叫 → 執行 callback
  │
  ├─ [僅 GET 請求]
  │    ├─ loadStatic() → 命中 → down() → 回傳 true（終止）
  │    └─ loadCache()  → 命中 → down() → 回傳 true（終止）
  │
  ├─ loadController() → 命中 → down() → 回傳 true（終止）
  │
  └─ 無任何路徑處理
       └─ 若 http_response_code() === 200 → 設為 400
          回傳 false
```

`up()` 回傳 `false` 時，`run()` 立即回傳 `false`。

## 3. 靜態檔案路徑（`loadStatic()`，僅 GET）

```
取得 static/paths（陣列）與 Req::uri()

依序搜尋每個 path：
  存在 {path}/{uri} → currentPath 命中，跳出

currentPath 為 null → 回傳 false（繼續下一階段）

檢查副檔名：
  無副檔名       → 嘗試 mime_content_type()
  副檔名 = 'php' → 拒絕，回傳 false
  其他副檔名     → 查 $_mimeMapping；查無則 mime_content_type()

MIME 無法確定 → 回傳 false

輸出：
  header('Content-Type: {mimeType}')
  header('Content-Length: {filesize}')
  echo file_get_contents(currentPath)

回傳 true（請求結束）
```

支援的 MIME 映射：`html`, `css`, `js`, `json`, `xml`, `jpg`, `png`, `gif`, `woff`, `ttf`。

## 4. 快取路徑（`loadCache()`，僅 GET）

```
取得 cache/path

快取檔路徑 = {cache/path}/{md5(Req::uri())}

檔案不存在 → 回傳 false

time() - filectime(快取檔) > cache/time（預設 300 秒）
  → unlink(快取檔)，回傳 false

命中有效快取：
  Res::html(file_get_contents(快取檔))

回傳 true（請求結束）
```

## 5. Controller 路由（`loadController()`）

### 5.1 URI 切段

```
Req::uri()        ← PATH_INFO 優先，否則 REQUEST_URI 去 query string
去頭尾 '/' 後切段 → params = ['seg0', 'seg1', ...]
```

### 5.2 路徑比對（貪婪逐段）

```
對每個 param[0]：
  tempPath = ucfirst(param[0])
  tempPath = currentPath + '/' + tempPath   （若 currentPath 已存在）

  存在 {controller/path}/{tempPath}/                   → 進入子目錄，繼續消費
  存在 {controller/path}/{tempPath}Controller.php      → 命中，停止消費
  兩者皆不存在                                          → 跳出迴圈

currentPath = 最後命中的路徑段（如 "Api/Rest"）
```

### 5.3 Fallback 路由

```
currentPath 為 null（無命中）
  │
  ├─ 嘗試 router/controller/default（ucfirst，預設 'Main'）
  │    └─ 存在 {controller/path}/{Default}Controller.php → currentPath = Default
  │
  └─ 不存在 → http_response_code(400)，回傳 false
```

### 5.4 類名組合與實例化

```
className = namespace + '\' + currentPath（'/' 換成 '\'） + 'Controller'
例：namespace='App\Controller', currentPath='Api/Rest'
    → App\Controller\Api\RestController

instance = new $className()
```

## 6. Controller 分流（依 `mode` 屬性）

### 6.1 Page 模式

```
取得 action 名稱：
  params 非空 → actionName = array_shift(params)
  否則         → actionName = router/action/default（預設 'default'）

方法 {actionName}Action 不存在：
  → 切換至 router/controller/default + router/action/error（預設 'error'）
  → 預設 Controller 不存在或 errorAction 不存在 → http_response_code(404)，回傳 false

View 初始化：
  View::init()
  setAttr('paths', view/paths)
  setAttr('ext', view/ext)
  setLayoutPath('{currentPath}/{actionName}')   ← 小寫化

Controller 執行：
  instance->up()
    └─ 回傳非 false
         instance->{actionName}Action($params)
           └─ 回傳非 false
                View::render()    ← index → loadLayout → loadContent
                saveCache(html)   （僅 GET 請求）
                Res::html(html)

instance->down()
  └─ 回傳 false → loadController() 回傳 false
```

#### 快取寫入（`saveCache()`）

```
cache/path 不存在時先 mkdir（使用 cache/permission，遞迴）
寫入 {cache/path}/{md5(Req::uri())} = html 內容
```

### 6.2 Ajax 模式

```
取得 action 名稱：（同 Page 模式）

方法 {actionName}Action 不存在：
  → http_response_code(501)，回傳 false

Controller 執行：
  instance->up()
    └─ 回傳非 false
         $data = instance->{actionName}Action($params)
         Res::json($data)

instance->down()
```

### 6.3 Rest 模式

```
actionName = Req::method()    ← 如 'get'、'post'、'put'、'delete'
                                  支援 HTTP_X_HTTP_METHOD_OVERRIDE

方法 {actionName}Action 不存在：
  → http_response_code(501)，回傳 false

Controller 執行：
  instance->up()
    └─ 回傳非 false
         $result = instance->{actionName}Action($params)
         Res::json($result)

instance->down()
```

## 7. View 渲染流程

```
View::render()
  │
  └─ loadPartial(indexPath)        ← 預設 'index'
       │
       └─ 模板內呼叫 $this->loadLayout()
            │
            └─ loadPartial(layoutPath)   ← 預設 'layout'
                 │
                 └─ 模板內呼叫 $this->loadContent()
                      └─ loadPartial(contentPath)
                           ← setLayoutPath() 所指定的 controller/action 路徑

模板搜尋規則：
  路徑以 '~' 或 '/' 開頭 → 直接解析為絕對路徑
  否則 → 依序搜尋 view/paths 陣列中的各目錄

setData(array) 的鍵值在模板 scope 中以變數形式展開（extract）
```

## 8. 完整流程圖

```
HTTP Request
  └─ index.php
       └─ App::run()
            ├─ up()
            │    ├─ [驗證] namespace & path
            │    ├─ Loader::append()
            │    └─ router/event/up()
            │
            ├─ [GET only]
            │    ├─ loadStatic()
            │    │    └─ 命中 → header + echo → down() → 結束
            │    └─ loadCache()
            │         └─ 命中 → Res::html() → down() → 結束
            │
            ├─ loadController()
            │    ├─ Req::uri() 切段路由 → currentPath
            │    │    └─ 無命中 → fallback: default → 400
            │    ├─ new {namespace}\{currentPath}Controller()
            │    └─ 依 mode 分流
            │         ├─ page  → action → View::render() → saveCache() → Res::html()
            │         ├─ ajax  → action → Res::json()
            │         └─ rest  → method action → Res::json()
            │
            ├─ down()
            │    └─ router/event/down()
            │
            └─ 無匹配 → http_response_code(400)

HTTP Response
```

## 9. 錯誤路徑摘要

| 條件 | HTTP 狀態 | 結果 |
|---|---|---|
| namespace 或 path 未設定 | — | throw Exception |
| Controller 無命中且 default 不存在 | 400 | `loadController()` 回傳 `false` |
| Page/Ajax action 不存在（含 error fallback 失敗） | 404 | 回傳 `false` |
| Ajax/Rest action 不存在 | 501 | 回傳 `false` |
| 靜態檔案為 `.php` | — | `loadStatic()` 回傳 `false`，繼續下一階段 |
| 快取過期 | — | 刪除快取，`loadCache()` 回傳 `false` |
| 無任何路徑處理（狀態仍 200） | 400 | `run()` 回傳 `false` |
