FROM golang:1.24.2-alpine AS builder

WORKDIR /src

RUN apk add --no-cache git

COPY go.mod go.sum ./
RUN go mod download

COPY . .

ARG GOOS=linux
ARG GOARCH=amd64
ENV CGO_ENABLED=0 GOOS=${GOOS} GOARCH=${GOARCH}

RUN go build -ldflags="-s -w" -o /app/server ./cmd


FROM alpine:3.19

RUN apk add --no-cache ca-certificates curl

RUN addgroup -S app && adduser -S -G app app

WORKDIR /app

COPY --from=builder /app/server /app/server
COPY --from=builder /src/.env /app/.env
RUN chown app:app /app/server /app/.env

EXPOSE 3000
ENV PORT=3000

USER app

HEALTHCHECK --interval=30s --timeout=5s --start-period=5s --retries=3 \
  CMD curl -f http://127.0.0.1:${PORT}/health || exit 1

CMD ["/app/server"]
