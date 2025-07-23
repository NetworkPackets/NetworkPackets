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
