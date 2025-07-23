# 🌍 NetworkPackets

A simple script(s) to display and log **network packet statistics** from log file in a clean, color-coded web interface.

![NetworkPackets Screenshot](https://i.imgur.com/SMZ4q3H.png)

---

## Features ✨

- 📊 Shows latest network packet stats from all interfaces  
- 📅 Timestamps formatted for easy reading  
- 🔍 View details per record by ID  
- 📈 Highlights max RX (receive) and TX (transmit) packets per second  
- 🎨 Stylish dark terminal-style UI with green text  
- 🕒 Performance info: script execution time & memory usage  

---

## How It Works ⚙️

- Reads data from `packet_stats_all_interfaces.txt` (pipe `|` delimited)  
- Parses and sanitizes the lines  
- Tracks max RX and TX packet values with timestamps  
- Displays a list (default last 20 records) with RX/TX, UDP, and ICMP packet stats  
- Clicking on a record ID shows detailed info for that record  

---

## Requirements 🛠️

- PHP 7.x or higher  
- Web server with PHP support (Apache, Nginx, etc.)  
- `packet_stats_all_interfaces.txt` log file in the same directory  

---

## System Permissions 🔐

The script ( packets.sh ) expects access to Linux network interface stats, so your environment must allow:

  - 📂 Reading network interface details from **`/sys/class/net`**  
  - ⚙️ Access to system commands like **`netstat`** to gather live network data  

---

## 📄 Log File Format

Each line contains 12 pipe-separated columns ( packet_stats_all_interfaces.txt ):

ID | Timestamp | Interface | RX pps | TX pps | Total RX | Total TX | UDP RX | UDP TX | ICMP RX | ICMP TX | Total Packets

```
| Field          | Description                                      | Example        |
|----------------|--------------------------------------------------|----------------|
| `ID`           | Unique record identifier                          | `12345`        |
| `Timestamp`    | UNIX timestamp of the record                      | `1690200000`   |
| `Interface`    | Network interface name (or `*`)                   |       `*`      |
| `RX pps`       | Received packets per second                       | `500`          |
| `TX pps`       | Transmitted packets per second                    | `480`          |
| `Total RX`     | Total packets received                            | `1050000`      |
| `Total TX`     | Total packets transmitted                         | `1020000`      |
| `UDP RX`       | Total UDP packets received                        | `250000`       |
| `UDP TX`       | Total UDP packets transmitted                     | `240000`       |
| `ICMP RX`      | Total ICMP packets received                       | `5000`         |
| `ICMP TX`      | Total ICMP packets transmitted                    | `4800`         |
| `Total Packets`| Total packets (received + transmitted)            | `2070000`      |
```

---

## Usage 🏃‍♂️

1. Place `packet_stats_all_interfaces.txt` in the same directory as the script ( this file is automatically created )
2. Run packets.sh in tmux or screen
3. Open the PHP script ( index.php or api.php ) in a browser via your web server  
4. View network packet statistics updated live from the log

---

## Configuration ⚙️

You can adjust these at the top of the PHP file:

```php
$logfile     = __DIR__ . '/packet_stats_all_interfaces.txt'; // log file path
$limit       = 20;                                         // number of recent records to display
$dateFormat  = 'Y-m-d H:i:s';                              // timestamp format

// UI colors
$bgColor     = '#000';
$textColor   = '#0f0';
$headerBg    = '#050';
$rowAltBg    = '#020';
```
