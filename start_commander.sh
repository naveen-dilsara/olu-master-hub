#!/bin/bash
echo "Starting OLU Master Hub Commander..."
echo "Server running at: http://localhost:8000"
echo "Press Ctrl+C to stop."
php -S localhost:8000 -t commander/public
