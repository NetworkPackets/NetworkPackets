# 📚 API Documentation for Packet Stats API

This API provides JSON data parsed from the `packet_stats_all_interfaces.txt` file, which contains network packet statistics for all interfaces.

---

## 🔗 Base URL

GET /api.php

---

## ⚙️ API Endpoints & Parameters

### 1. Get latest packet records (default)

- **Request:**

GET /api.php

- **Response:**

```json
{
  "limit": 20,
  "latest_total_packets": 2070000,
  "total_rx_pps": 12345,
  "total_tx_pps": 11000,
  "records": [
    {
      "id": 12345,
      "ts": 1690200000,
      "time": "2023-07-23 10:00:00",
      "iface": "eth0",
      "rx": 500,
      "tx": 480,
      "totRx": 1050000,
      "totTx": 1020000,
      "udp_rx": 250000,
      "udp_tx": 240000,
      "icmp_rx": 5000,
      "icmp_tx": 4800,
      "total_packets": 2070000
    },
    ...
  ]
}
```

2. Get a single record by ID

Request:
```
GET /api.php?id={record_id}
```

Example:
```
GET /api.php?id=12345
```

Response:
```
{
  "id": 12345,
  "ts": 1690200000,
  "time": "2023-07-23 10:00:00",
  "iface": "eth0",
  "rx": 500,
  "tx": 480,
  "totRx": 1050000,
  "totTx": 1020000,
  "udp_rx": 250000,
  "udp_tx": 240000,
  "icmp_rx": 5000,
  "icmp_tx": 4800,
  "total_packets": 2070000
}
```

Error (if ID not found):
```
    {
      "error": "ID 12345 not found"
    }
```

3. Get summary statistics
Request:
GET /api.php?summary

Response:
```
    {
      "max_rx": {
        "value": 600,
        "time": "2023-07-23 11:00:00"
      },
      "max_tx": {
        "value": 580,
        "time": "2023-07-23 11:00:00"
      },
      "rx_pps": 12345,
      "tx_pps": 11000,
      "latest_total_packets": 2070000,
      "record_count": 1500,
      "db_size_bytes": 2345678
    }
```

🔍 Field Definitions
```
id	Unique record identifier
ts	UNIX timestamp of the record
time	Human-readable timestamp (Y-m-d H:i:s)
iface	Network interface name (e.g. eth0)
rx	Received packets per second (pps)
tx	Transmitted packets per second (pps)
totRx	Total received packets (cumulative)
totTx	Total transmitted packets (cumulative)
udp_rx	Total UDP packets received
udp_tx	Total UDP packets transmitted
icmp_rx	Total ICMP packets received
icmp_tx	Total ICMP packets transmitted
total_packets	Total packets (received + transmitted)
```

⚠️ Notes
```
- The API reads the log file packet_stats_all_interfaces.txt located in the same directory.
- The file must be readable by the web server.
- Limit parameter is capped at 500 for performance.
- Timestamps are UNIX epoch integers.
```

🚀 Example Usage
```
Fetch record with ID 100:
curl "https://server/api.php?id=100"

Get summary:
curl "https://server/api.php?summary"
```
