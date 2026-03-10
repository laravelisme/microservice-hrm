package config

import (
	"bytes"
	"encoding/json"

	"github.com/gofiber/fiber/v2"
	"github.com/gofiber/fiber/v2/middleware/compress"
	"github.com/gofiber/fiber/v2/middleware/cors"
	"github.com/gofiber/fiber/v2/middleware/etag"
	"github.com/gofiber/fiber/v2/middleware/helmet"
	"github.com/gofiber/fiber/v2/middleware/logger"
	"github.com/gofiber/fiber/v2/middleware/recover"
	"github.com/joho/godotenv"
)

func customJSONEncoder(v interface{}) ([]byte, error) {
	buffer := &bytes.Buffer{}
	encoder := json.NewEncoder(buffer)
	encoder.SetEscapeHTML(false)
	err := encoder.Encode(v)
	if err != nil {
		return nil, err
	}
	return buffer.Bytes(), nil
}

func NewFiber() *fiber.App {
	if err := godotenv.Load(); err != nil {
		panic("Error loading .env file")
	}

	app := fiber.New(fiber.Config{
		Prefork:       true,
		CaseSensitive: true,
		StrictRouting: true,
		ServerHeader:  "TAKO ANUGERAH KORPORASI",
		AppName:       "Hrm-transaction-service",
		JSONEncoder:   customJSONEncoder,
		JSONDecoder:   json.Unmarshal,
	})

	app.Use(cors.New(cors.Config{
		AllowHeaders: "Origin,Content-Type,Accept,User-Agent,Content-Length,Authorization",
		AllowOrigins: "*",
		AllowMethods: "GET,POST,HEAD,PUT,DELETE,PATCH,OPTIONS",
	}))

	app.Use(helmet.New())
	app.Use(compress.New())
	app.Use(etag.New())
	app.Use(recover.New())

	app.Use(logger.New(logger.Config{
		Format:     "${pid} ${status} - ${method} ${path}\n",
		TimeFormat: "02-Jan-2006",
		TimeZone:   "Asia/Jakarta",
	}))

	return app
}
