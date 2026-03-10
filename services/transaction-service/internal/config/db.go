package config

import (
	"fmt"
	"github.com/joho/godotenv"
	"gorm.io/driver/postgres"
	"gorm.io/gen"
	"gorm.io/gorm"
	"gorm.io/gorm/logger"
	"log"
	"os"
	"time"
)

var DB *gorm.DB

func InitDB() {
	var (
		err error
		dsn string
	)

	if err = godotenv.Load(); err != nil {
		panic("Error loading .env file")
	}

	// Build Postgres DSN
	dsn = fmt.Sprintf(
		"host=%s user=%s password=%s dbname=%s port=%s sslmode=disable TimeZone=Asia/Jakarta",
		os.Getenv("DB_HOST"),
		os.Getenv("DB_USER"),
		os.Getenv("DB_PASSWORD"),
		os.Getenv("DB_NAME"),
		os.Getenv("DB_PORT"),
	)

	newLogger := logger.New(
		log.New(os.Stdout, "\r\n", log.LstdFlags),
		logger.Config{
			SlowThreshold:             time.Second,
			LogLevel:                  logger.Info,
			Colorful:                  true,
			IgnoreRecordNotFoundError: true,
		},
	)

	// Retry loop: wait for DB to be ready
	maxAttempts := 12
	for attempts := 1; attempts <= maxAttempts; attempts++ {
		DB, err = gorm.Open(postgres.Open(dsn), &gorm.Config{
			Logger: newLogger,
		})
		if err == nil {
			break
		}
		log.Printf("Attempt %d/%d: DB connection failed: %v\n", attempts, maxAttempts, err)
		if attempts < maxAttempts {
			time.Sleep(3 * time.Second)
		}
	}

	if err != nil {
		log.Fatalf("Error connecting to database after retries: %v", err)
	}

	g := gen.NewGenerator(gen.Config{
		OutPath:      "internal/model/database",
		OutFile:      "gen.go",
		Mode:         gen.WithoutContext | gen.WithDefaultQuery | gen.WithQueryInterface,
		ModelPkgPath: "database",
	})

	g.UseDB(DB)

	log.Println("Database connected")
}

func GetDB() *gorm.DB {
	if DB == nil {
		InitDB()
	}
	return DB
}
