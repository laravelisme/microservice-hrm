package common

import (
	"time"
)

func FormatTime(t *time.Time) *string {
	if t == nil {
		return nil
	}
	formatted := t.Format("2006-01-02 15:04:05")
	return &formatted
}
