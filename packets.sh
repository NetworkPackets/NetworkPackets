#!/bin/bash

# Read secret 
SECRET=$(<secret)

# Output log file
OUTPUT_FILE="${SECRET}_packet_stats_all_interfaces.txt"

# Interval between measurements
INTERVAL=1

# ID tracking
ID_FILE="last_id.txt"

# Previous packet counters
prev_rx=0
prev_tx=0

# Load last ID if it exists
if [ -f "$ID_FILE" ]; then
  id_counter=$(<"$ID_FILE")
else
  id_counter=1
fi

while true; do
  # Sum RX packets across all interfaces
  total_curr_rx=$(awk '{s+=$1} END {print s}' /sys/class/net/*/statistics/rx_packets)
  total_curr_tx=$(awk '{s+=$1} END {print s}' /sys/class/net/*/statistics/tx_packets)

  # Calculate RX/TX per second
  if [[ $prev_rx -gt 0 && $prev_tx -gt 0 ]]; then
    rx_pps=$((total_curr_rx - prev_rx))
    tx_pps=$((total_curr_tx - prev_tx))
  else
    rx_pps=0
    tx_pps=0
  fi

  # Get UDP and ICMP stats
  udp_rx=$(netstat -s | awk '/Udp:/,/^$/' | grep 'packets received' | awk '{print $1}')
  udp_tx=$(netstat -s | awk '/Udp:/,/^$/' | grep 'packets sent' | awk '{print $1}')
  icmp_rx=$(netstat -s | awk '/Icmp:/,/^$/' | grep 'messages received' | awk '{print $1}')
  icmp_tx=$(netstat -s | awk '/Icmp:/,/^$/' | grep 'messages sent' | awk '{print $1}')

  # Current timestamp
  timestamp=$(date +%s)

  # Total combined packets
  total_packets=$((total_curr_rx + total_curr_tx))

  # Write to log file
  echo "$id_counter|$timestamp|*|$rx_pps|$tx_pps|$total_curr_rx|$total_curr_tx|$udp_rx|$udp_tx|$icmp_rx|$icmp_tx|$total_packets" >> "$OUTPUT_FILE"

  # Save last values for next iteration
  prev_rx=$total_curr_rx
  prev_tx=$total_curr_tx

  # Increment ID and save
  ((id_counter++))
  echo "$id_counter" > "$ID_FILE"

  sleep "$INTERVAL"
done
