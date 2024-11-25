from pyngrok import ngrok

# Define the port to forward
port = 8080

# Create a tunnel to the PHP server running on localhost:8080
public_url = ngrok.connect(port)

# Print the public URL created by ngrok
print(f"Your server is accessible at: {public_url}")
