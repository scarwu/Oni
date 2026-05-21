# Oni Core 規格

## 範圍
本文件描述 `src/Oni/Core` 的現行行為規格，涵蓋 `Basic` 與 `Loader`。

## `Oni\Core\Basic`

### 目的
提供共用屬性容器，供 App、Controller、Task、View 等類別繼承。

### 狀態
- `protected array $_attr = []`

### 介面
- `setAttr(string $key, mixed $value): bool`
  - 將 `$value` 寫入 `$_attr[$key]`。
  - 永遠回傳 `true`。
- `getAttr(string $key): mixed`
  - 若 key 存在，回傳對應值。
  - 若不存在，回傳 `null`。

### 行為約束
- 不做 key 格式驗證。
- 支援任意 `mixed` 值。
- 不提供刪除、合併、唯讀保護。

## `Oni\Core\Loader`

### 目的
提供命名空間到實體路徑的動態 autoload。

### 狀態
- `private static $_instance = null`（懶初始化）
- `private static $_namespaceList = []`（`namespace => path[]`）

### 註冊流程
- 呼叫 `append(string $namespace, string $path): bool` 時：
  - 首次呼叫會建立 singleton 並註冊 `spl_autoload_register`。
  - `namespace` 會 `trim($namespace, '\\')`。
  - `path` 會 `rtrim($path, '/')`。
  - 相同 namespace 可註冊多個 path，依加入順序搜尋。
  - 永遠回傳 `true`。

### Autoload 解析流程
對每次 class 請求：
1. 去除 class 前後反斜線。
2. 逐一檢查已註冊 namespace 是否為前綴。
3. 命中後移除 namespace 前綴，將 `\\` 轉為 `/`。
4. 依序檢查每個 path 的 `"{$path}/{$className}.php"`。
5. 找到即 `require` 並回傳 `true`；都找不到回傳 `false`。

### 行為特性與限制
- 可重複 append 相同 namespace/path（不去重）。
- 無移除註冊、無快取、無 classmap。
- 依前綴比對與註冊順序決定優先度。
