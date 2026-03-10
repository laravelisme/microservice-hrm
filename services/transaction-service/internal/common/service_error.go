package common

import (
	"errors"
	"gorm.io/gorm"
	"net/http"
)

type ServiceErrorDto struct {
	Message    string
	Err        error
	StatusCode int
}

func CustomError(message string, err error, statusCode int) *ServiceErrorDto {
	return &ServiceErrorDto{Message: message, Err: err, StatusCode: statusCode}
}

func InternalServiceError(err error) *ServiceErrorDto {
	if errors.Is(gorm.ErrRecordNotFound, err) {
		return nil
	}
	return CustomError(err.Error(), err, http.StatusInternalServerError)
}
