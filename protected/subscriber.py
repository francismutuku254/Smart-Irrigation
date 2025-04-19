import paho.mqtt.client as mqtt
import mysql.connector
from datetime import datetime, timedelta
from pytz import timezone
import json
import time

# Timezone Configuration
EAT = timezone("Africa/Nairobi")

# MQTT Configuration
MQTT_BROKER = "64.181.230.240"
MQTT_SENSORS_TOPIC = "sensors/moisture"
MQTT_PUMP_TOPIC = "test/topic"
MQTT_STATUS_TOPIC = "pump/status"
MQTT_CLIENT_ID = "SoilMoistureController"

# Database Configuration
DB_HOST = "localhost"
DB_USER = "peter_richu"
DB_PASSWORD = "Peter"
DB_NAME = "mydatabase"

# Track last published command
last_published_command = None

# Global Database Connection
def connect_to_db():
    try:
        connection = mysql.connector.connect(
            host=DB_HOST,
            user=DB_USER,
            password=DB_PASSWORD,
            database=DB_NAME,
            autocommit=True
        )
        print("Database connection established.")
        return connection
    except mysql.connector.Error as e:
        print(f"Error connecting to database: {e}")
        return None

# Reconnect database if necessary
def ensure_db_connection():
    global db_connection, cursor
    try:
        db_connection.ping(reconnect=True)
    except mysql.connector.Error as e:
        print(f"Database connection lost. Reconnecting... Error: {e}")
        db_connection = connect_to_db()
        if db_connection:
            cursor = db_connection.cursor()

# Establish initial connection
db_connection = connect_to_db()
if db_connection:
    cursor = db_connection.cursor()

# Insert soil moisture data into the database
def insert_soil_data(device_id, moisture, message):
    ensure_db_connection()
    try:
        query = "INSERT INTO soil_data (device_id, moisture, server_time, message) VALUES (%s, %s, %s, %s)"
        values = (device_id, moisture, datetime.now(EAT), message)
        cursor.execute(query, values)
    except mysql.connector.Error as e:
        print(f"Error inserting soil data: {e}")

# Insert pump status data into the database
def insert_pump_status(status, source):
    ensure_db_connection()
    try:
        if status in ["On", "Off"] and source in ["Manual", "Automatic"]:
            query = "INSERT INTO pump_status (status, source, server_time) VALUES (%s, %s, %s)"
            values = (status, source, datetime.now(EAT))
            cursor.execute(query, values)
            print(f"Inserted pump status: {status} - {source}")
        else:
            print("Invalid pump status data received.")
    except mysql.connector.Error as e:
        print(f"Error inserting pump status: {e}")

# Calculate average soil moisture for the last 6 hours
def calculate_overall_avg():
    ensure_db_connection()
    try:
        six_hours_ago = datetime.now(EAT) - timedelta(hours=6)
        query = "SELECT AVG(moisture) FROM soil_data WHERE server_time >= %s"
        cursor.execute(query, (six_hours_ago,))
        result = cursor.fetchone()
        return result[0] if result[0] is not None else 0
    except mysql.connector.Error as e:
        print(f"Error calculating average: {e}")
        return 0

# Fetch the latest thresholds
def fetch_latest_thresholds():
    ensure_db_connection()
    try:
        query = "SELECT top_threshold, bottom_threshold FROM thresholds ORDER BY created_at DESC LIMIT 1"
        cursor.execute(query)
        result = cursor.fetchone()
        return {"top_threshold": result[0], "bottom_threshold": result[1]} if result else None
    except mysql.connector.Error as e:
        print(f"Error fetching thresholds: {e}")
        return None

# Publish pump control commands
def publish_pump_command(command):
    global last_published_command
    if command != last_published_command:
        payload = json.dumps({"command": command, "source": "Automatic"})
        client.publish(MQTT_PUMP_TOPIC, payload)
        print(f"Published: {payload}")
        last_published_command = command

# Handle incoming MQTT messages
def on_message(client, userdata, message):
    try:
        payload = message.payload.decode().strip()
        
        if payload.startswith('"') and payload.endswith('"'):
            payload = payload[1:-1]

        print(f"Received message from topic {message.topic}: {payload}")

        payload_data = json.loads(payload)

        if message.topic == MQTT_SENSORS_TOPIC:
            device_id = payload_data["device_id"]
            moisture = payload_data["moisture"]
            message_content = payload_data.get("message", "")

            insert_soil_data(device_id, moisture, message_content)

            overall_avg = calculate_overall_avg()
            thresholds = fetch_latest_thresholds()
            if thresholds:
                top_threshold = thresholds["top_threshold"]
                bottom_threshold = thresholds["bottom_threshold"]

                if overall_avg < bottom_threshold:
                    publish_pump_command("ON")
                elif overall_avg > top_threshold:
                    publish_pump_command("OFF")

        elif message.topic == MQTT_STATUS_TOPIC:
            status = payload_data.get("status")
            source = payload_data.get("source")
            insert_pump_status(status, source)

    except json.JSONDecodeError as e:
        print(f"Error processing message: {e}")

# Setup MQTT client
client = mqtt.Client(MQTT_CLIENT_ID)
client.on_message = on_message

# Connect to the broker
client.connect(MQTT_BROKER)

# Subscribe to topics
client.subscribe([(MQTT_SENSORS_TOPIC, 0), (MQTT_STATUS_TOPIC, 0)])
print("Listening for MQTT messages...")

client.loop_start()

# Keep the script running
try:
    while True:
        ensure_db_connection()
        time.sleep(60)
except KeyboardInterrupt:
    print("Stopping the script...")
    client.loop_stop()
    if cursor:
        cursor.close()
    if db_connection:
        db_connection.close()
