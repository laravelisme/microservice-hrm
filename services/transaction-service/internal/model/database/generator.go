package database

import (
	db "github.com/laravel2004/auth-service/internal/config"
	"gorm.io/gen"
	"log"
)

func main() {
	g := gen.NewGenerator(gen.Config{
		OutPath:      "internal/model/database",
		OutFile:      "gen.go",
		Mode:         gen.WithoutContext | gen.WithDefaultQuery | gen.WithQueryInterface,
		ModelPkgPath: "database",
	})

	g.UseDB(db.GetDB())

	g.GenerateAllTable()

	//for _, table := range tables {
	//	g.ApplyBasic(table)
	//}

	g.Execute()

	log.Println("Database model generation complete!")
}
