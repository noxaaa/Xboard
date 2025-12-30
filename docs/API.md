# Xboard API Documentation

本文档详细描述了 Xboard 系统的所有 API 接口，包括请求方法、参数、响应格式和示例。

## 目录

- [基础信息](#基础信息)
- [API V1](#api-v1)
  - [Guest (公开接口)](#guest-公开接口)
  - [Passport (认证接口)](#passport-认证接口)
  - [User (用户接口)](#user-用户接口)
  - [Client (客户端接口)](#client-客户端接口)
  - [Server (服务器接口)](#server-服务器接口)
- [API V2](#api-v2)
  - [Admin (管理后台接口)](#admin-管理后台接口)

---

## 基础信息

### Base URL

```
/api/v1  - V1 版本接口
/api/v2  - V2 版本接口
```

### 认证方式

大部分接口需要在请求头中携带 Bearer Token：

```http
Authorization: Bearer <token>
```

### 响应格式

所有接口返回 JSON 格式，成功响应结构：

```json
{
  "data": <响应数据>
}
```

错误响应结构：

```json
{
  "message": "错误信息"
}
```

---

# API V1

## Guest (公开接口)

无需认证即可访问的公开接口。

### 获取系统配置

获取前端所需的系统配置信息。

- **URL**: `/api/v1/guest/comm/config`
- **Method**: `GET`
- **认证**: 不需要

**响应示例**:

```json
{
  "data": {
    "tos_url": "https://example.com/tos",
    "is_email_verify": 1,
    "is_invite_force": 0,
    "email_whitelist_suffix": ["gmail.com", "qq.com"],
    "is_captcha": 1,
    "captcha_type": "recaptcha",
    "recaptcha_site_key": "6LcXXXXXXXXXXXXX",
    "recaptcha_v3_site_key": "6LcXXXXXXXXXXXXX",
    "recaptcha_v3_score_threshold": 0.5,
    "turnstile_site_key": "0x4AAAAAAXXXXXXX",
    "app_description": "高速稳定的代理服务",
    "app_url": "https://example.com",
    "logo": "https://example.com/logo.png"
  }
}
```

---

### 获取订阅计划列表

获取所有可用的订阅计划。

- **URL**: `/api/v1/guest/plan/fetch`
- **Method**: `GET`
- **认证**: 不需要

**响应示例**:

```json
{
  "data": [
    {
      "id": 1,
      "name": "基础套餐",
      "content": "适合轻度用户",
      "month_price": 1000,
      "quarter_price": 2500,
      "half_year_price": 4500,
      "year_price": 8000,
      "transfer_enable": 107374182400,
      "device_limit": 3,
      "speed_limit": null
    }
  ]
}
```

---

### Telegram Webhook

Telegram Bot 消息推送回调接口。

- **URL**: `/api/v1/guest/telegram/webhook`
- **Method**: `POST`
- **认证**: 不需要

**请求体**: Telegram Update 对象

---

### 支付通知回调

支付网关异步通知接口。

- **URL**: `/api/v1/guest/payment/notify/{method}/{uuid}`
- **Method**: `GET` / `POST`
- **认证**: 不需要
- **路径参数**:
  - `method`: 支付方式标识
  - `uuid`: 订单唯一标识

---

## Passport (认证接口)

用户注册、登录相关接口。

### 用户注册

- **URL**: `/api/v1/passport/auth/register`
- **Method**: `POST`
- **认证**: 不需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| email | string | 是 | 邮箱地址 |
| password | string | 是 | 密码 |
| email_code | string | 视配置 | 邮箱验证码（开启邮箱验证时必填） |
| invite_code | string | 视配置 | 邀请码（开启强制邀请时必填） |
| recaptcha_data | string | 视配置 | 人机验证数据 |

**请求示例**:

```json
{
  "email": "user@example.com",
  "password": "your_password",
  "email_code": "123456",
  "invite_code": "ABC123"
}
```

**响应示例**:

```json
{
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "auth_data": "encrypted_auth_data"
  }
}
```

---

### 用户登录

- **URL**: `/api/v1/passport/auth/login`
- **Method**: `POST`
- **认证**: 不需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| email | string | 是 | 邮箱地址 |
| password | string | 是 | 密码 |

**请求示例**:

```json
{
  "email": "user@example.com",
  "password": "your_password"
}
```

**响应示例**:

```json
{
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "auth_data": "encrypted_auth_data"
  }
}
```

---

### Token 登录

通过 token 或验证码登录。

- **URL**: `/api/v1/passport/auth/token2Login`
- **Method**: `GET`
- **认证**: 不需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| token | string | 否 | 登录 token（用于跳转） |
| verify | string | 否 | 验证码（用于邮件链接登录） |
| redirect | string | 否 | 登录后跳转地址 |

---

### 忘记密码

- **URL**: `/api/v1/passport/auth/forget`
- **Method**: `POST`
- **认证**: 不需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| email | string | 是 | 邮箱地址 |
| email_code | string | 是 | 邮箱验证码 |
| password | string | 是 | 新密码 |

**请求示例**:

```json
{
  "email": "user@example.com",
  "email_code": "123456",
  "password": "new_password"
}
```

---

### 获取快速登录 URL

- **URL**: `/api/v1/passport/auth/getQuickLoginUrl`
- **Method**: `POST`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| auth_data | string | 否 | 认证数据 |
| redirect | string | 否 | 跳转地址 |

---

### 邮件链接登录

通过邮件发送登录链接。

- **URL**: `/api/v1/passport/auth/loginWithMailLink`
- **Method**: `POST`
- **认证**: 不需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| email | string | 是 | 邮箱地址 |
| redirect | string | 否 | 登录后跳转地址 |

---

### 发送邮箱验证码

- **URL**: `/api/v1/passport/comm/sendEmailVerify`
- **Method**: `POST`
- **认证**: 不需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| email | string | 是 | 邮箱地址 |
| recaptcha_data | string | 视配置 | 人机验证数据 |

---

### 邀请码访问统计

记录邀请码页面访问。

- **URL**: `/api/v1/passport/comm/pv`
- **Method**: `POST`
- **认证**: 不需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| invite_code | string | 是 | 邀请码 |

---

## User (用户接口)

需要用户认证的接口，请求头需携带 `Authorization: Bearer <token>`。

### 获取用户信息

- **URL**: `/api/v1/user/info`
- **Method**: `GET`
- **认证**: 需要

**响应示例**:

```json
{
  "data": {
    "email": "user@example.com",
    "transfer_enable": 107374182400,
    "last_login_at": 1703836800,
    "created_at": 1703750400,
    "banned": 0,
    "remind_expire": 1,
    "remind_traffic": 1,
    "expired_at": 1735689600,
    "balance": 10000,
    "commission_balance": 5000,
    "plan_id": 1,
    "discount": null,
    "commission_rate": null,
    "telegram_id": null,
    "uuid": "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx",
    "avatar_url": "https://cdn.v2ex.com/gravatar/xxx?s=64&d=identicon"
  }
}
```

---

### 获取用户统计

- **URL**: `/api/v1/user/getStat`
- **Method**: `GET`
- **认证**: 需要

**响应示例**:

```json
{
  "data": [
    1,  // 待支付订单数
    2,  // 待处理工单数
    5   // 邀请用户数
  ]
}
```

---

### 获取订阅信息

- **URL**: `/api/v1/user/getSubscribe`
- **Method**: `GET`
- **认证**: 需要

**响应示例**:

```json
{
  "data": {
    "plan_id": 1,
    "token": "xxxxxxxxxxxxxxxx",
    "expired_at": 1735689600,
    "u": 1073741824,
    "d": 5368709120,
    "transfer_enable": 107374182400,
    "email": "user@example.com",
    "uuid": "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx",
    "device_limit": 3,
    "speed_limit": null,
    "next_reset_at": 1704067200,
    "plan": {
      "id": 1,
      "name": "基础套餐"
    },
    "subscribe_url": "https://example.com/api/v1/client/subscribe?token=xxx",
    "reset_day": 15
  }
}
```

---

### 重置订阅密钥

重置用户的 UUID 和 Token。

- **URL**: `/api/v1/user/resetSecurity`
- **Method**: `GET`
- **认证**: 需要

**响应示例**:

```json
{
  "data": "https://example.com/api/v1/client/subscribe?token=new_token"
}
```

---

### 修改密码

- **URL**: `/api/v1/user/changePassword`
- **Method**: `POST`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| old_password | string | 是 | 旧密码 |
| new_password | string | 是 | 新密码 |

---

### 更新用户设置

- **URL**: `/api/v1/user/update`
- **Method**: `POST`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| remind_expire | integer | 否 | 到期提醒（0/1） |
| remind_traffic | integer | 否 | 流量提醒（0/1） |

---

### 佣金转余额

- **URL**: `/api/v1/user/transfer`
- **Method**: `POST`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| transfer_amount | integer | 是 | 转账金额（分） |

---

### 检查登录状态

- **URL**: `/api/v1/user/checkLogin`
- **Method**: `GET`
- **认证**: 需要

**响应示例**:

```json
{
  "data": {
    "is_login": true,
    "is_admin": false
  }
}
```

---

### 获取活跃会话

- **URL**: `/api/v1/user/getActiveSession`
- **Method**: `GET`
- **认证**: 需要

---

### 移除活跃会话

- **URL**: `/api/v1/user/removeActiveSession`
- **Method**: `POST`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| session_id | string | 是 | 会话 ID |

---

### 获取快速登录 URL

- **URL**: `/api/v1/user/getQuickLoginUrl`
- **Method**: `POST`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| redirect | string | 否 | 跳转地址 |

---

## 订单相关

### 创建订单

- **URL**: `/api/v1/user/order/save`
- **Method**: `POST`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| plan_id | integer | 是 | 套餐 ID |
| period | string | 是 | 周期（month_price/quarter_price/half_year_price/year_price/onetime_price/reset_price） |
| coupon_code | string | 否 | 优惠券码 |

**请求示例**:

```json
{
  "plan_id": 1,
  "period": "month_price",
  "coupon_code": "DISCOUNT10"
}
```

**响应示例**:

```json
{
  "data": "202312280001"
}
```

---

### 订单结账

- **URL**: `/api/v1/user/order/checkout`
- **Method**: `POST`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| trade_no | string | 是 | 订单号 |
| method | integer | 是 | 支付方式 ID |
| token | string | 否 | Stripe token（Stripe 支付时需要） |

**响应示例**:

```json
{
  "type": 1,
  "data": "https://payment-gateway.com/pay/xxx"
}
```

type 说明：
- `-1`: 免费订单，直接完成
- `0`: 二维码支付
- `1`: 跳转支付链接

---

### 获取订单列表

- **URL**: `/api/v1/user/order/fetch`
- **Method**: `GET`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| status | integer | 否 | 订单状态（0-待支付，1-开通中，2-已取消，3-已完成） |

---

### 获取订单详情

- **URL**: `/api/v1/user/order/detail`
- **Method**: `GET`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| trade_no | string | 是 | 订单号 |

---

### 检查订单状态

- **URL**: `/api/v1/user/order/check`
- **Method**: `GET`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| trade_no | string | 是 | 订单号 |

**响应示例**:

```json
{
  "data": 0
}
```

状态说明：0-待支付，1-开通中，2-已取消，3-已完成

---

### 取消订单

- **URL**: `/api/v1/user/order/cancel`
- **Method**: `POST`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| trade_no | string | 是 | 订单号 |

---

### 获取支付方式

- **URL**: `/api/v1/user/order/getPaymentMethod`
- **Method**: `GET`
- **认证**: 需要

**响应示例**:

```json
{
  "data": [
    {
      "id": 1,
      "name": "支付宝",
      "payment": "Alipay",
      "icon": "alipay",
      "handling_fee_fixed": 0,
      "handling_fee_percent": 0
    }
  ]
}
```

---

## 套餐相关

### 获取可用套餐

- **URL**: `/api/v1/user/plan/fetch`
- **Method**: `GET`
- **认证**: 需要

---

## 邀请相关

### 生成邀请码

- **URL**: `/api/v1/user/invite/save`
- **Method**: `GET`
- **认证**: 需要

---

### 获取邀请信息

- **URL**: `/api/v1/user/invite/fetch`
- **Method**: `GET`
- **认证**: 需要

**响应示例**:

```json
{
  "data": {
    "codes": [
      {
        "id": 1,
        "code": "ABC12345",
        "status": 0,
        "pv": 10
      }
    ],
    "stat": [
      5,      // 已注册用户数
      50000,  // 有效佣金
      10000,  // 确认中佣金
      10,     // 佣金比例
      35000   // 可用佣金
    ]
  }
}
```

---

### 获取邀请详情

- **URL**: `/api/v1/user/invite/details`
- **Method**: `GET`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| current | integer | 否 | 页码，默认 1 |
| page_size | integer | 否 | 每页数量，默认 10 |

---

## 公告相关

### 获取公告列表

- **URL**: `/api/v1/user/notice/fetch`
- **Method**: `GET`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| current | integer | 否 | 页码，默认 1 |

---

## 工单相关

### 获取工单列表/详情

- **URL**: `/api/v1/user/ticket/fetch`
- **Method**: `GET`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| id | integer | 否 | 工单 ID（传入时获取详情） |

---

### 创建工单

- **URL**: `/api/v1/user/ticket/save`
- **Method**: `POST`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| subject | string | 是 | 工单标题 |
| level | integer | 是 | 优先级（0-低，1-中，2-高） |
| message | string | 是 | 工单内容 |

---

### 回复工单

- **URL**: `/api/v1/user/ticket/reply`
- **Method**: `POST`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| id | integer | 是 | 工单 ID |
| message | string | 是 | 回复内容 |

---

### 关闭工单

- **URL**: `/api/v1/user/ticket/close`
- **Method**: `POST`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| id | integer | 是 | 工单 ID |

---

### 提现申请

- **URL**: `/api/v1/user/ticket/withdraw`
- **Method**: `POST`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| withdraw_method | string | 是 | 提现方式 |
| withdraw_account | string | 是 | 提现账号 |

---

## 服务器相关

### 获取服务器列表

- **URL**: `/api/v1/user/server/fetch`
- **Method**: `GET`
- **认证**: 需要

支持 ETag 缓存，返回头包含 `ETag`，请求头可携带 `If-None-Match` 进行缓存验证。

---

## 优惠券相关

### 验证优惠券

- **URL**: `/api/v1/user/coupon/check`
- **Method**: `POST`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| code | string | 是 | 优惠券码 |
| plan_id | integer | 否 | 套餐 ID |
| period | string | 否 | 周期 |

---

## 礼品卡相关

### 验证礼品卡

- **URL**: `/api/v1/user/gift-card/check`
- **Method**: `POST`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| code | string | 是 | 礼品卡码 |

**响应示例**:

```json
{
  "data": {
    "code_info": {
      "template_name": "新年礼包",
      "template_type": "balance"
    },
    "reward_preview": {
      "balance": 10000
    },
    "can_redeem": true,
    "reason": null
  }
}
```

---

### 兑换礼品卡

- **URL**: `/api/v1/user/gift-card/redeem`
- **Method**: `POST`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| code | string | 是 | 礼品卡码 |

---

### 获取礼品卡使用记录

- **URL**: `/api/v1/user/gift-card/history`
- **Method**: `GET`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| page | integer | 否 | 页码 |
| per_page | integer | 否 | 每页数量（1-100） |

---

### 获取礼品卡详情

- **URL**: `/api/v1/user/gift-card/detail`
- **Method**: `GET`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| id | integer | 是 | 使用记录 ID |

---

### 获取礼品卡类型

- **URL**: `/api/v1/user/gift-card/types`
- **Method**: `GET`
- **认证**: 需要

---

## Telegram 相关

### 获取 Bot 信息

- **URL**: `/api/v1/user/telegram/getBotInfo`
- **Method**: `GET`
- **认证**: 需要

---

## 知识库相关

### 获取知识库文章

- **URL**: `/api/v1/user/knowledge/fetch`
- **Method**: `GET`
- **认证**: 需要

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| id | integer | 否 | 文章 ID（传入时获取单篇） |
| language | string | 否 | 语言 |
| keyword | string | 否 | 搜索关键词 |

---

### 获取知识库分类

- **URL**: `/api/v1/user/knowledge/getCategory`
- **Method**: `GET`
- **认证**: 需要

---

## 统计相关

### 获取流量日志

- **URL**: `/api/v1/user/stat/getTrafficLog`
- **Method**: `GET`
- **认证**: 需要

---

## 通用接口

### 获取用户配置

- **URL**: `/api/v1/user/comm/config`
- **Method**: `GET`
- **认证**: 需要

---

### 获取 Stripe 公钥

- **URL**: `/api/v1/user/comm/getStripePublicKey`
- **Method**: `POST`
- **认证**: 需要

---

## Client (客户端接口)

客户端订阅相关接口，需要客户端认证。

### 获取订阅配置

通过订阅链接获取节点配置。

- **URL**: `/api/v1/client/subscribe`
- **Method**: `GET`
- **认证**: client middleware (token 参数)

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| types | string | 否 | 节点类型过滤（hysteria,vless,vmess,trojan 等，用 `|` 或 `,` 分隔） |
| filter | string | 否 | 节点名称/标签过滤 |
| flag | string | 否 | 客户端标识（用于适配不同客户端格式） |

**请求示例**:

```
GET /api/v1/client/subscribe?token=xxx&types=vmess|trojan&filter=香港
```

**订阅链接格式**:

```
https://example.com/s/{token}
```

---

### 获取应用配置

- **URL**: `/api/v1/client/app/getConfig`
- **Method**: `GET`
- **认证**: client middleware

---

### 获取应用版本

- **URL**: `/api/v1/client/app/getVersion`
- **Method**: `GET`
- **认证**: client middleware

---

## Server (服务器接口)

节点后端通信接口，需要服务器认证。

### UniProxy 接口

#### 获取节点配置

- **URL**: `/api/v1/server/UniProxy/config`
- **Method**: `GET`
- **认证**: server middleware

支持 ETag 缓存。

---

#### 获取用户列表

- **URL**: `/api/v1/server/UniProxy/user`
- **Method**: `GET`
- **认证**: server middleware

支持 ETag 缓存。

---

#### 推送流量数据

- **URL**: `/api/v1/server/UniProxy/push`
- **Method**: `POST`
- **认证**: server middleware

**请求体**: 二维数组，每项为 `[用户ID, 流量字节数]`

```json
[
  [1, 1073741824],
  [2, 536870912]
]
```

---

#### 推送在线数据

- **URL**: `/api/v1/server/UniProxy/alive`
- **Method**: `POST`
- **认证**: server middleware

---

#### 获取在线用户列表

- **URL**: `/api/v1/server/UniProxy/alivelist`
- **Method**: `GET`
- **认证**: server middleware

---

#### 推送节点状态

- **URL**: `/api/v1/server/UniProxy/status`
- **Method**: `POST`
- **认证**: server middleware

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| cpu | number | 是 | CPU 使用率（0-100） |
| mem.total | integer | 是 | 总内存（字节） |
| mem.used | integer | 是 | 已用内存（字节） |
| swap.total | integer | 是 | 总交换空间 |
| swap.used | integer | 是 | 已用交换空间 |
| disk.total | integer | 是 | 总磁盘空间 |
| disk.used | integer | 是 | 已用磁盘空间 |

---

### ShadowsocksTidalab 接口

#### 获取用户列表

- **URL**: `/api/v1/server/ShadowsocksTidalab/user`
- **Method**: `GET`
- **认证**: server middleware (shadowsocks)

---

#### 提交数据

- **URL**: `/api/v1/server/ShadowsocksTidalab/submit`
- **Method**: `POST`
- **认证**: server middleware (shadowsocks)

---

### TrojanTidalab 接口

#### 获取配置

- **URL**: `/api/v1/server/TrojanTidalab/config`
- **Method**: `GET`
- **认证**: server middleware (trojan)

---

#### 获取用户列表

- **URL**: `/api/v1/server/TrojanTidalab/user`
- **Method**: `GET`
- **认证**: server middleware (trojan)

---

#### 提交数据

- **URL**: `/api/v1/server/TrojanTidalab/submit`
- **Method**: `POST`
- **认证**: server middleware (trojan)

---

# API V2

## Admin (管理后台接口)

管理后台接口，需要管理员认证。

**注意**: Admin 接口的前缀是动态的，由 `secure_path` 配置决定，默认格式为：

```
/api/v2/{secure_path}/...
```

### 系统配置

#### 获取配置

- **URL**: `/api/v2/{secure_path}/config/fetch`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 保存配置

- **URL**: `/api/v2/{secure_path}/config/save`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 获取邮件模板

- **URL**: `/api/v2/{secure_path}/config/getEmailTemplate`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 获取主题模板

- **URL**: `/api/v2/{secure_path}/config/getThemeTemplate`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 设置 Telegram Webhook

- **URL**: `/api/v2/{secure_path}/config/setTelegramWebhook`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 测试发送邮件

- **URL**: `/api/v2/{secure_path}/config/testSendMail`
- **Method**: `POST`
- **认证**: admin middleware

---

### 套餐管理

#### 获取套餐列表

- **URL**: `/api/v2/{secure_path}/plan/fetch`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 创建套餐

- **URL**: `/api/v2/{secure_path}/plan/save`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 更新套餐

- **URL**: `/api/v2/{secure_path}/plan/update`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 删除套餐

- **URL**: `/api/v2/{secure_path}/plan/drop`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 套餐排序

- **URL**: `/api/v2/{secure_path}/plan/sort`
- **Method**: `POST`
- **认证**: admin middleware

---

### 服务器管理

#### 服务器分组

##### 获取分组列表

- **URL**: `/api/v2/{secure_path}/server/group/fetch`
- **Method**: `GET`
- **认证**: admin middleware

---

##### 保存分组

- **URL**: `/api/v2/{secure_path}/server/group/save`
- **Method**: `POST`
- **认证**: admin middleware

---

##### 删除分组

- **URL**: `/api/v2/{secure_path}/server/group/drop`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 服务器路由

##### 获取路由列表

- **URL**: `/api/v2/{secure_path}/server/route/fetch`
- **Method**: `GET`
- **认证**: admin middleware

---

##### 保存路由

- **URL**: `/api/v2/{secure_path}/server/route/save`
- **Method**: `POST`
- **认证**: admin middleware

---

##### 删除路由

- **URL**: `/api/v2/{secure_path}/server/route/drop`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 服务器节点

##### 获取节点列表

- **URL**: `/api/v2/{secure_path}/server/manage/getNodes`
- **Method**: `GET`
- **认证**: admin middleware

---

##### 保存节点

- **URL**: `/api/v2/{secure_path}/server/manage/save`
- **Method**: `POST`
- **认证**: admin middleware

---

##### 更新节点

- **URL**: `/api/v2/{secure_path}/server/manage/update`
- **Method**: `POST`
- **认证**: admin middleware

---

##### 删除节点

- **URL**: `/api/v2/{secure_path}/server/manage/drop`
- **Method**: `POST`
- **认证**: admin middleware

---

##### 复制节点

- **URL**: `/api/v2/{secure_path}/server/manage/copy`
- **Method**: `POST`
- **认证**: admin middleware

---

##### 节点排序

- **URL**: `/api/v2/{secure_path}/server/manage/sort`
- **Method**: `POST`
- **认证**: admin middleware

---

### 订单管理

#### 获取订单列表

- **URL**: `/api/v2/{secure_path}/order/fetch`
- **Method**: `GET` / `POST`
- **认证**: admin middleware

---

#### 更新订单

- **URL**: `/api/v2/{secure_path}/order/update`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 指派订单

- **URL**: `/api/v2/{secure_path}/order/assign`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 标记已支付

- **URL**: `/api/v2/{secure_path}/order/paid`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 取消订单

- **URL**: `/api/v2/{secure_path}/order/cancel`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 获取订单详情

- **URL**: `/api/v2/{secure_path}/order/detail`
- **Method**: `POST`
- **认证**: admin middleware

---

### 用户管理

#### 获取用户列表

- **URL**: `/api/v2/{secure_path}/user/fetch`
- **Method**: `GET` / `POST`
- **认证**: admin middleware

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| current | integer | 否 | 页码 |
| pageSize | integer | 否 | 每页数量 |
| filter | array | 否 | 过滤条件 |
| sort | array | 否 | 排序条件 |

**过滤条件示例**:

```json
{
  "filter": [
    {"id": "email", "value": "test"},
    {"id": "plan_id", "value": [1, 2, 3]},
    {"id": "expired_at", "value": "lt:1735689600"}
  ],
  "sort": [
    {"id": "created_at", "desc": true}
  ]
}
```

---

#### 获取用户详情

- **URL**: `/api/v2/{secure_path}/user/getUserInfoById`
- **Method**: `GET`
- **认证**: admin middleware

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| id | integer | 是 | 用户 ID |

---

#### 更新用户

- **URL**: `/api/v2/{secure_path}/user/update`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 生成用户

- **URL**: `/api/v2/{secure_path}/user/generate`
- **Method**: `POST`
- **认证**: admin middleware

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| email_prefix | string | 否 | 邮箱前缀（单个生成时） |
| email_suffix | string | 是 | 邮箱后缀 |
| password | string | 否 | 密码 |
| plan_id | integer | 否 | 套餐 ID |
| expired_at | integer | 否 | 过期时间戳 |
| generate_count | integer | 否 | 批量生成数量 |
| download_csv | boolean | 否 | 是否下载 CSV |

---

#### 导出用户 CSV

- **URL**: `/api/v2/{secure_path}/user/dumpCSV`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 发送邮件

- **URL**: `/api/v2/{secure_path}/user/sendMail`
- **Method**: `POST`
- **认证**: admin middleware

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| subject | string | 是 | 邮件主题 |
| content | string | 是 | 邮件内容 |
| filter | array | 否 | 用户过滤条件 |

---

#### 封禁用户

- **URL**: `/api/v2/{secure_path}/user/ban`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 重置用户密钥

- **URL**: `/api/v2/{secure_path}/user/resetSecret`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 设置邀请人

- **URL**: `/api/v2/{secure_path}/user/setInviteUser`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 删除用户

- **URL**: `/api/v2/{secure_path}/user/destroy`
- **Method**: `POST`
- **认证**: admin middleware

**请求参数**:

| 参数名 | 类型 | 必填 | 描述 |
|--------|------|------|------|
| id | integer | 是 | 用户 ID |

---

### 统计数据

#### 获取概览数据

- **URL**: `/api/v2/{secure_path}/stat/getOverride`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 获取统计数据

- **URL**: `/api/v2/{secure_path}/stat/getStats`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 获取服务器实时排名

- **URL**: `/api/v2/{secure_path}/stat/getServerLastRank`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 获取服务器昨日排名

- **URL**: `/api/v2/{secure_path}/stat/getServerYesterdayRank`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 获取订单统计

- **URL**: `/api/v2/{secure_path}/stat/getOrder`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 获取用户统计

- **URL**: `/api/v2/{secure_path}/stat/getStatUser`
- **Method**: `GET` / `POST`
- **认证**: admin middleware

---

#### 获取排名数据

- **URL**: `/api/v2/{secure_path}/stat/getRanking`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 获取统计记录

- **URL**: `/api/v2/{secure_path}/stat/getStatRecord`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 获取流量排名

- **URL**: `/api/v2/{secure_path}/stat/getTrafficRank`
- **Method**: `GET`
- **认证**: admin middleware

---

### 公告管理

#### 获取公告列表

- **URL**: `/api/v2/{secure_path}/notice/fetch`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 保存公告

- **URL**: `/api/v2/{secure_path}/notice/save`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 更新公告

- **URL**: `/api/v2/{secure_path}/notice/update`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 删除公告

- **URL**: `/api/v2/{secure_path}/notice/drop`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 显示/隐藏公告

- **URL**: `/api/v2/{secure_path}/notice/show`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 公告排序

- **URL**: `/api/v2/{secure_path}/notice/sort`
- **Method**: `POST`
- **认证**: admin middleware

---

### 工单管理

#### 获取工单列表

- **URL**: `/api/v2/{secure_path}/ticket/fetch`
- **Method**: `GET` / `POST`
- **认证**: admin middleware

---

#### 回复工单

- **URL**: `/api/v2/{secure_path}/ticket/reply`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 关闭工单

- **URL**: `/api/v2/{secure_path}/ticket/close`
- **Method**: `POST`
- **认证**: admin middleware

---

### 优惠券管理

#### 获取优惠券列表

- **URL**: `/api/v2/{secure_path}/coupon/fetch`
- **Method**: `GET` / `POST`
- **认证**: admin middleware

---

#### 生成优惠券

- **URL**: `/api/v2/{secure_path}/coupon/generate`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 删除优惠券

- **URL**: `/api/v2/{secure_path}/coupon/drop`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 启用/禁用优惠券

- **URL**: `/api/v2/{secure_path}/coupon/show`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 更新优惠券

- **URL**: `/api/v2/{secure_path}/coupon/update`
- **Method**: `POST`
- **认证**: admin middleware

---

### 礼品卡管理

#### 模板管理

##### 获取模板列表

- **URL**: `/api/v2/{secure_path}/gift-card/templates`
- **Method**: `GET` / `POST`
- **认证**: admin middleware

---

##### 创建模板

- **URL**: `/api/v2/{secure_path}/gift-card/create-template`
- **Method**: `POST`
- **认证**: admin middleware

---

##### 更新模板

- **URL**: `/api/v2/{secure_path}/gift-card/update-template`
- **Method**: `POST`
- **认证**: admin middleware

---

##### 删除模板

- **URL**: `/api/v2/{secure_path}/gift-card/delete-template`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 兑换码管理

##### 生成兑换码

- **URL**: `/api/v2/{secure_path}/gift-card/generate-codes`
- **Method**: `POST`
- **认证**: admin middleware

---

##### 获取兑换码列表

- **URL**: `/api/v2/{secure_path}/gift-card/codes`
- **Method**: `GET` / `POST`
- **认证**: admin middleware

---

##### 启用/禁用兑换码

- **URL**: `/api/v2/{secure_path}/gift-card/toggle-code`
- **Method**: `POST`
- **认证**: admin middleware

---

##### 导出兑换码

- **URL**: `/api/v2/{secure_path}/gift-card/export-codes`
- **Method**: `GET`
- **认证**: admin middleware

---

##### 更新兑换码

- **URL**: `/api/v2/{secure_path}/gift-card/update-code`
- **Method**: `POST`
- **认证**: admin middleware

---

##### 删除兑换码

- **URL**: `/api/v2/{secure_path}/gift-card/delete-code`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 使用记录

- **URL**: `/api/v2/{secure_path}/gift-card/usages`
- **Method**: `GET` / `POST`
- **认证**: admin middleware

---

#### 统计数据

- **URL**: `/api/v2/{secure_path}/gift-card/statistics`
- **Method**: `GET` / `POST`
- **认证**: admin middleware

---

#### 获取类型

- **URL**: `/api/v2/{secure_path}/gift-card/types`
- **Method**: `GET`
- **认证**: admin middleware

---

### 知识库管理

#### 获取文章列表

- **URL**: `/api/v2/{secure_path}/knowledge/fetch`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 获取分类

- **URL**: `/api/v2/{secure_path}/knowledge/getCategory`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 保存文章

- **URL**: `/api/v2/{secure_path}/knowledge/save`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 显示/隐藏文章

- **URL**: `/api/v2/{secure_path}/knowledge/show`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 删除文章

- **URL**: `/api/v2/{secure_path}/knowledge/drop`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 文章排序

- **URL**: `/api/v2/{secure_path}/knowledge/sort`
- **Method**: `POST`
- **认证**: admin middleware

---

### 支付管理

#### 获取支付方式列表

- **URL**: `/api/v2/{secure_path}/payment/fetch`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 获取可用支付方式

- **URL**: `/api/v2/{secure_path}/payment/getPaymentMethods`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 获取支付表单

- **URL**: `/api/v2/{secure_path}/payment/getPaymentForm`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 保存支付方式

- **URL**: `/api/v2/{secure_path}/payment/save`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 删除支付方式

- **URL**: `/api/v2/{secure_path}/payment/drop`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 启用/禁用支付方式

- **URL**: `/api/v2/{secure_path}/payment/show`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 支付方式排序

- **URL**: `/api/v2/{secure_path}/payment/sort`
- **Method**: `POST`
- **认证**: admin middleware

---

### 系统管理

#### 获取系统状态

- **URL**: `/api/v2/{secure_path}/system/getSystemStatus`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 获取队列统计

- **URL**: `/api/v2/{secure_path}/system/getQueueStats`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 获取队列负载

- **URL**: `/api/v2/{secure_path}/system/getQueueWorkload`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 获取队列主管理器

- **URL**: `/api/v2/{secure_path}/system/getQueueMasters`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 获取系统日志

- **URL**: `/api/v2/{secure_path}/system/getSystemLog`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 获取失败任务

- **URL**: `/api/v2/{secure_path}/system/getHorizonFailedJobs`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 清除系统日志

- **URL**: `/api/v2/{secure_path}/system/clearSystemLog`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 获取日志清理统计

- **URL**: `/api/v2/{secure_path}/system/getLogClearStats`
- **Method**: `GET`
- **认证**: admin middleware

---

### 主题管理

#### 获取主题列表

- **URL**: `/api/v2/{secure_path}/theme/getThemes`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 上传主题

- **URL**: `/api/v2/{secure_path}/theme/upload`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 删除主题

- **URL**: `/api/v2/{secure_path}/theme/delete`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 保存主题配置

- **URL**: `/api/v2/{secure_path}/theme/saveThemeConfig`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 获取主题配置

- **URL**: `/api/v2/{secure_path}/theme/getThemeConfig`
- **Method**: `POST`
- **认证**: admin middleware

---

### 插件管理

#### 获取插件类型

- **URL**: `/api/v2/{secure_path}/plugin/types`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 获取插件列表

- **URL**: `/api/v2/{secure_path}/plugin/getPlugins`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 上传插件

- **URL**: `/api/v2/{secure_path}/plugin/upload`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 删除插件

- **URL**: `/api/v2/{secure_path}/plugin/delete`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 安装插件

- **URL**: `/api/v2/{secure_path}/plugin/install`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 卸载插件

- **URL**: `/api/v2/{secure_path}/plugin/uninstall`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 启用插件

- **URL**: `/api/v2/{secure_path}/plugin/enable`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 禁用插件

- **URL**: `/api/v2/{secure_path}/plugin/disable`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 获取插件配置

- **URL**: `/api/v2/{secure_path}/plugin/config`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 更新插件配置

- **URL**: `/api/v2/{secure_path}/plugin/config`
- **Method**: `POST`
- **认证**: admin middleware

---

#### 升级插件

- **URL**: `/api/v2/{secure_path}/plugin/upgrade`
- **Method**: `POST`
- **认证**: admin middleware

---

### 流量重置管理

#### 获取重置日志

- **URL**: `/api/v2/{secure_path}/traffic-reset/logs`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 获取重置统计

- **URL**: `/api/v2/{secure_path}/traffic-reset/stats`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 获取用户重置历史

- **URL**: `/api/v2/{secure_path}/traffic-reset/user/{userId}/history`
- **Method**: `GET`
- **认证**: admin middleware

---

#### 手动重置用户流量

- **URL**: `/api/v2/{secure_path}/traffic-reset/reset-user`
- **Method**: `POST`
- **认证**: admin middleware

---

## 错误码说明

| 错误码 | 描述 |
|--------|------|
| 400 | 请求参数错误 |
| 401 | 未授权 |
| 403 | 禁止访问 |
| 404 | 资源不存在 |
| 422 | 参数验证失败 |
| 500 | 服务器内部错误 |

---

## 附录

### 订单状态

| 状态码 | 描述 |
|--------|------|
| 0 | 待支付 |
| 1 | 开通中 |
| 2 | 已取消 |
| 3 | 已完成 |

### 工单状态

| 状态码 | 描述 |
|--------|------|
| 0 | 待处理 |
| 1 | 已关闭 |

### 工单优先级

| 优先级 | 描述 |
|--------|------|
| 0 | 低 |
| 1 | 中 |
| 2 | 高 |

### 订阅周期

| 周期标识 | 描述 |
|----------|------|
| month_price | 月付 |
| quarter_price | 季付 |
| half_year_price | 半年付 |
| year_price | 年付 |
| two_year_price | 两年付 |
| three_year_price | 三年付 |
| onetime_price | 一次性 |
| reset_price | 流量重置 |

### 支持的节点类型

- `shadowsocks`
- `vmess`
- `vless`
- `trojan`
- `hysteria`
- `tuic`
- `socks`
- `anytls`
- `naive`
- `http`
- `mieru`
