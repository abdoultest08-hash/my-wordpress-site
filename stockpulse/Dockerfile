FROM python:3.11-slim

# System dependencies for psycopg2 and feedparser
RUN apt-get update && apt-get install -y --no-install-recommends \
    gcc \
    libpq-dev \
    python3-sgmllib3k \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Install Python dependencies first (cached layer)
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copy application code
COPY . .

# Create directories that need to exist at runtime
RUN mkdir -p logs data

# Railway injects PORT — not used by StockPulse but good practice
ENV PORT=8080
ENV PYTHONUNBUFFERED=1

# Initialise the database on first run, then start the scheduler
CMD ["sh", "-c", "python database/init_db.py && python scheduler.py"]
