# Oni Web 規格

## 範圍
本文件描述 `src/Oni/Web` 的現行行為：`App`、Controller 基類、`Req`、`Res`、`View`、Store、`HTML`。

## `Oni\Web\App`

### 預設屬性
- 路由：`router/controller/default=main`、`router/action/default=default`、`router/action/error=error`
- 必要設定：`controller/namespace`、`controller/path`、`view/paths`、`static/paths`、`cache/path`
- 其他：`view/ext=php`、`cache/permission=0775`、`cache/time=300`

### `run()` 執行順序
1. `up()`：註冊 controller namespace/path，執行 `router/event/up`。
2. `GET` 請求先嘗試：
   - `loadStatic()`：從 `static/paths` 找檔案，拒絕 `.php`，輸出內容與 MIME。
   - `loadCache()`：讀 `cache/path/md5(uri)`；若過期（`filectime`）先刪除。
3. `loadController()`：路由並執行 controller。
4. 全部未處理時，若狀態碼仍為 200，改設 400。

### Controller 路由
- 以 `Req::uri()`（去頭尾 `/`）切段，逐段 `ucfirst` 嘗試目錄或 `Controller.php`。
- 無命中時 fallback 到 `router/controller/default`。
- 類名格式：`{controller/namespace}\{Path\To\Controller}Controller`。

### 三種模式
- `page`
  - action 來自下一段參數，否則 `router/action/default`。
  - action 不存在時 fallback 到預設 controller 的 `router/action/error`；再失敗回傳 404。
  - 成功 action 後 `View::render()`，GET 會 `saveCache()`，最後 `Res::html()`。
- `ajax`
  - action 規則同 page；不存在回傳 501。
  - action 回傳值以 `Res::json()` 輸出。
- `rest`
  - action 名為 HTTP method（如 `getAction`/`postAction`）；不存在回傳 501。
  - 回傳值以 `Res::json()` 輸出。

## Controller 基類
- `Page`/`Ajax`/`Rest` 皆繼承 `Basic`，預設 `mode` 分別為 `page`/`ajax`/`rest`。
- 皆提供 `up()`（預設 `true`）與 `down()`（空實作）。

## `Oni\Web\Http\Req`
- `method()` 支援 `HTTP_X_HTTP_METHOD_OVERRIDE`。
- `uri()` 優先 `PATH_INFO`，否則 `REQUEST_URI` 去 query string。
- `content()`：
  - `application/x-www-form-urlencoded`、`multipart/form-data` -> `$_POST`
  - `application/json` -> `json_decode(body, true)`
  - 其他 -> 原始 body 字串
- 提供 `query()`、`file()`、`isAjax()` 等存取器。

## `Oni\Web\Http\Res`
- `redirect($path)` 設定 `Location` header。
- `html($data)` 與 `json($data)` 皆設定 `Content-Type` 與 `Content-Length` 後輸出。

## `Oni\Web\View`
- Singleton；預設 `indexPath='index'`、`layoutPath='layout'`、`contentPath=null`。
- `render()` 僅渲染 index，index 內可呼叫 `$this->loadLayout()`，layout 內可呼叫 `$this->loadContent()`。
- 模板搜尋：
  - 目標路徑以 `~` 或 `/` 開頭時，直接找絕對路徑 `.{ext}`。
  - 否則在 `view/paths` 依序尋找。
- `setData()` 會將資料鍵值展開成模板區域變數。

## 其他元件
- `Model`: 空基類（僅繼承 `Basic`）。
- `Store\Cache::init()`：回傳 `Memcached` singleton（目前未使用 `$config`）。
- `Store\Database::init()`：以 `$config[host|port|name|user|pass]` 建立 MySQL `PDO` singleton，並設定 UTF-8。
- `Helper\HTML`：`linkEncode()` 對路徑 segment 做 `rawurlencode`，`linkTo()` 產生 `<a>`。
