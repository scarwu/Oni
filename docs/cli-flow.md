# CLI APP 運作流程

## 範圍

描述從入口腳本啟動到 Task 執行完畢的完整流程，以及各關鍵路徑的決策邏輯。

---

## 1. 初始化

```
boot.php
  │
  ├─ new Oni\CLI\App()
  │    └─ IO::init()        ← 解析 $_SERVER['argv']，建立 singleton
  │
  ├─ $app->setAttr('task/namespace', ...)
  ├─ $app->setAttr('task/path', ...)
  ├─ $app->setAttr('router/task/default', ...)   ← 選填，預設 'main'
  └─ $app->run()
```

## 2. `run()` 主流程

```
run()
  │
  ├─ up()
  │    ├─ 驗證 task/namespace、task/path 皆為字串
  │    │    └─ 否 → throw Exception('oni:exception:namespaceOrPathNotSet')
  │    ├─ Loader::append(namespace, path)   ← 註冊 autoload
  │    └─ 若 router/event/up 可呼叫 → 執行 callback
  │
  ├─ loadTask()   ← 路由與執行（詳見第 3 節）
  │
  └─ down()
       └─ 若 router/event/down 可呼叫 → 執行 callback
```

`up()` 或 `loadTask()` 回傳 `false` 時，`run()` 立即回傳 `false` 並停止後續步驟。

## 3. Task 路由（`loadTask()`）

### 3.1 取得引數

```
IO::getArguments()
  └─ 回傳位置參數陣列（$_SERVER['argv'] 去除程式名後解析的 arguments 段）
```

### 3.2 路徑比對（貪婪逐段）

```
params = [seg0, seg1, seg2, ...]

對每個 param[0]：
  tempPath = ucfirst(param[0])
  tempPath = currentPath + '/' + tempPath   （若 currentPath 已存在）

  存在 {task/path}/{tempPath}/              → 進入子目錄，繼續消費下一段
  存在 {task/path}/{tempPath}Task.php       → 命中檔案，停止消費
  兩者皆不存在                               → 跳出迴圈，未消費的 params 保留

currentPath = 最後命中的路徑段（可能含子目錄，如 "Sub/Foo"）
```

### 3.3 Fallback 路由

```
currentPath 為 null（無任何命中）
  │
  ├─ 嘗試 router/task/default（ucfirst）
  │    └─ 存在 {task/path}/{Default}Task.php → currentPath = Default
  │
  ├─ 否則嘗試 router/task/error（ucfirst）
  │    └─ 存在 {task/path}/{Error}Task.php   → currentPath = Error
  │
  └─ 兩者皆不存在 → loadTask() 回傳 false
```

### 3.4 類名組合與實例化

```
className = namespace + '\' + currentPath（'/' 換成 '\'） + 'Task'
例：namespace='App\Task', currentPath='Sub/Foo'
    → App\Task\Sub\FooTask

instance = new $className()
```

## 4. Task 生命週期

```
instance->up()
  │
  ├─ 回傳 false → loadTask() 回傳 false（終止）
  │
  └─ 回傳非 false
       │
       instance->run($remainingParams)
         │   $remainingParams = 路由比對後未消費的位置參數
         │
         ├─ 回傳 false → loadTask() 回傳 false（終止）
         │
         └─ 回傳非 false
              │
              instance->down()
                └─ 回傳 false → loadTask() 回傳 false
```

## 5. IO 參數解析

```
$_SERVER['argv'] = ['script.php', 'foo', 'bar', '-v', '--debug=1']

解析結果：
  arguments : ['foo', 'bar']          ← 位置參數（用於路由與 run() 傳入）
  options   : ['v' => true]           ← -x 或 -x value
  configs   : ['debug' => '1']        ← --key 或 --key=value
```

`IO::getArguments()` 在路由階段被消費；剩餘的位置參數作為 `$params` 傳入 `run()`。

## 6. 完整流程圖

```
boot.php
  └─ App::run()
       ├─ up()
       │    ├─ [驗證] namespace & path
       │    ├─ Loader::append()
       │    └─ router/event/up()
       │
       ├─ loadTask()
       │    ├─ IO::getArguments()
       │    ├─ [路由] 逐段貪婪比對 → currentPath
       │    │    └─ 無命中 → fallback: default → error → false
       │    ├─ new {namespace}\{currentPath}Task()
       │    └─ task.up() → task.run($params) → task.down()
       │
       └─ down()
            └─ router/event/down()
```

## 7. 錯誤路徑摘要

| 條件 | 結果 |
|---|---|
| namespace 或 path 未設定 | throw Exception |
| 路由無命中且無 default/error task | `loadTask()` 回傳 `false` |
| `task->up()` 回傳 `false` | 中止，跳過 `run()`，`loadTask()` 回傳 `false` |
| `task->run()` 回傳 `false` | 中止，`loadTask()` 回傳 `false` |
| `task->down()` 回傳 `false` | `loadTask()` 回傳 `false` |
