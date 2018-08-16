# 小程序后台接口文档

## 概述

- 请求和服务器响应必须是`json`格式
- `POST`和`PUT`请求的参数不必区分数据类型，例如`"1499.99"`和`1499.99`视为相同参数
- 日期和时间统一为UNIX时间戳
- Response 示例
```json
{
    "id": "1",
    "code": "INSPECT001",
    "name": "1号运输机",
    "clients": [
        {
            "id": "1",
            "name": "大连军区"
        },
        {
            "id": "2",
            "name": "成都军区"
        }
    ]
}
```
