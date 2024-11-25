import http.server
import socketserver
from pyngrok import ngrok

# Define the port for the local server
PORT = 8080

# Create the request handler for the HTTP server
Handler = http.server.SimpleHTTPRequestHandler

# Start Ngrok and expose the local server on port 8080
def start_ngrok():
    # Set up a public URL for the local server using ngrok
    public_url = ngrok.connect(PORT)
    print(f"Ngrok tunnel \"{public_url}\" -> \"http://127.0.0.1:{PORT}\"")
    return public_url

# Start the local HTTP server
def start_server():
    # Start the Ngrok tunnel
    public_url = start_ngrok()

    # Start the local HTTP server on the specified port
    with socketserver.TCPServer(("", PORT), Handler) as httpd:
        print(f"Serving on port {PORT}...")
        httpd.serve_forever()

if __name__ == "__main__":
    start_server()
