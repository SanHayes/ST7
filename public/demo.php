<?php
// API 地址（替换 "你的token"）
$api_url = "https://quote.tradeswitcher.com/quote-b-api/batch-kline?token=79d4cac1cac608ae460af90dec8cad98-c-app";

// 请求体参数（Body JSON 数据）
$request_body = [
    "trace" => "c2a8a146-a647-4d6f-ac07-8c4805bf0b74", // 唯一追踪标识
    "data" => [
        "data_list" => [
            [
                "code" => "Silver",               // XAGUSD
                "kline_type" => 1,                // K 线类型
                "kline_timestamp_end" => 0,       // 结束时间戳
                "query_kline_num" => 1,           // 查询 K 线数量
                "adjust_type" => 0                // 复权类型
            ],
            [
                "code" => "GOLD",                // USOUSD
                "kline_type" => 1,
                "kline_timestamp_end" => 0,
                "query_kline_num" => 1,
                "adjust_type" => 0
            ],
            [
                "code" => "USOIL",                // USOUSD
                "kline_type" => 1,
                "kline_timestamp_end" => 0,
                "query_kline_num" => 1,
                "adjust_type" => 0
            ],
            [
                "code" => "UKOIL",                // UKOUSD
                "kline_type" => 1,
                "kline_timestamp_end" => 0,
                "query_kline_num" => 1,
                "adjust_type" => 0
            ]
        ]
    ]
];

// 初始化 cURL
$ch = curl_init();

// 设置 cURL 选项
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_body));

// 执行请求并获取响应
$response = curl_exec($ch);

// 检查是否有错误
if (curl_errno($ch)) {
    echo "cURL 错误: " . curl_error($ch);
} else {
    // 解析 JSON 响应
    $data = json_decode($response, true);
    echo "<pre>";
    var_dump($data);
}

// 关闭 cURL 资源
curl_close($ch);