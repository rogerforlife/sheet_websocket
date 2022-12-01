# sheet_websocket

此專案為線上多人表單編輯系統，採用PHP語言、Laravel框架實作。

重點項目:
1. 基本的CRUD上，且採用的是RESTful API風格。
2. 熟悉'workerman'，實作Websocket。
3. 增加紀錄log的middleware。
4. 使用trait來解決在單線物件繼承的限制，因為PHP不支持多重繼承，因此使用trait讓程式碼可複用。
5. 可以讓使用者下載表單內容成excel。

API:
1. 127.0.0.1/api/issue
  創建或刪除特定表單下issue資料，以RESTful API實作CRUD。
2. 127.0.0.1/api/sheet_download
  讓使用者下載表單內容成excel。
3. 127.0.0.1/api/fetch_data
  讓外部系統查詢資料，須夾帶apikey驗證。

Websocket:
1. LOCATION_CHANGE: 使用者在表單的位置
2. DATA_CHANGE: 更改issue的內容
3. ORDER_CHANGE: 更改issue的order
4. UPDATE_SHEET_SETTING: 更改表單的設定
5. CREATE_SHEET: 創建表單
6. DELETE_SHEET: 刪除表單
