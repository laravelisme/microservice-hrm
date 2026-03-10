package route

import (
	"github.com/gofiber/fiber/v2"
	v1 "github.com/laravel2004/auth-service/internal/router/v1"
)

func Init(c *fiber.App) {
	v1.InitUserRoute(c.Group("/v1/auth"))
	c.Get("/test", func(c *fiber.Ctx) error {
		return c.Status(fiber.StatusOK).JSON(fiber.Map{
			"message": "This is a test endpoint",
		})
	})
	c.Get("/", func(c *fiber.Ctx) error {
		return c.Status(fiber.StatusOK).JSON(fiber.Map{"status": "ok"})
	})

	c.Get("/api/v2/transaction-data/health", func(c *fiber.Ctx) error {
		return c.Status(fiber.StatusOK).JSON(fiber.Map{"status": "okewww"})
	})
}
