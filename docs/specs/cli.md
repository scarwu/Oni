# Oni CLI 規格

## 範圍
本文件描述 `src/Oni/CLI` 的現行行為：`App`、`Task`、`IO`、`Helper/ANSIEscapeCode`。

## `Oni\CLI\App`

### 預設屬性
- `router/event/up`: `null`
- `router/event/down`: `null`
- `router/task/default`: `main`
- `task/namespace`: `null`（必要）
- `task/path`: `null`（必要）

### 啟動流程
- `run()`:
  1. 執行 `up()`：若 namespace/path 皆為字串，呼叫 `Loader::append(...)`；若 `router/event/up` 可呼叫，回傳其結果。
  2. 只有 `up() !== false` 才進入 `loadTask()`。
  3. `loadTask()` 成功後執行 `down()` 並回傳 `true`，否則 `false`。

### Task 路由
- 來源：`IO::getArguments()`。
- 逐段嘗試（`ucfirst`）匹配：
  - 子目錄 `{$path}/{$tempPath}` 或
  - 檔案 `{$path}/{$tempPath}Task.php`。
- 若無任何命中，改用 `router/task/default`，需存在 `{$Default}Task.php`。
- 類名組合：`{task/namespace}\{Path\To\Task}Task`。

### Task 生命週期
- 執行順序：`up()` -> `run($params)` -> `down()`。
- `up() === false` 時跳過 `run`，但仍會呼叫 `down()`。

## `Oni\CLI\Task`
- 抽象基類；建構時注入 `IO::init()`。
- 預設 `up()` 回傳 `true`，`down()` 為空實作。
- 子類必須實作 `run(array $params = []): void`。

## `Oni\CLI\IO`

### 參數解析
- 啟動時解析 `$_SERVER['argv']`（略過程式名）為三類：
  - `arguments`: 一般位置參數。
  - `options`: `-x` 或 `-x value`。
  - `configs`: `--key` 或 `--key=value`。
- `get*` 不存在時回傳 `null`；`has*` 回傳布林。
- 型別簽章：
  - `getArguments(?int $index = null): array|string|null`
  - `getOptions(?string $key = null): array|string|null`
  - `getConfigs(?string $key = null): array|string|null`

### 輸入輸出
- `read()`: 讀 `STDIN` 並 `trim`。
- `ask()`: 重複詢問直到 callback 回傳 `true`。
- `write()/writeln()`: 支援前景/背景色。
- `error/warning/notice/info/debug/log`: 顏色化輸出封裝。

### `menuSelector()`
- 互動式選單，使用 `tput` 取得終端尺寸、`readline` callback 與 ANSI 控制碼。
- 支援方向鍵、PageUp/Down、Home/End、Enter。
- 回傳被選取 index（0-based），簽章為 `?int`。
- 當 `options` 為空時回傳 `null`。

## `Oni\CLI\Helper\ANSIEscapeCode`
- 提供 ANSI 常數、鍵碼常數與游標/顏色函式。
- `color()` 會包裝 SGR 起訖碼。
- `cursorSave()/cursorLoad()` 對 Apple Terminal 與其他終端使用不同控制碼。
