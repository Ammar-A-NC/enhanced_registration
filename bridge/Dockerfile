FROM python:3.12-alpine

RUN apk add --no-cache openldap-clients

WORKDIR /app
COPY src/bridge.py /app/bridge.py

ENV PYTHONUNBUFFERED=1
ENV PYTHONDONTWRITEBYTECODE=1

EXPOSE 18080

CMD ["python3", "/app/bridge.py"]
