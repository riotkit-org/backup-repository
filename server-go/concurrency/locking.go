package concurrency

import (
	"database/sql"
	"errors"
	"fmt"
	"gorm.io/gorm"
	"math/rand"
	"time"
)

type LocksService struct {
	db *gorm.DB
}

func (ls *LocksService) Lock(id string, howLong time.Duration) (Lock, error) {
	if ls.isLockedAlready(id) {
		return Lock{}, errors.New("already locked")
	}
	if err := ls.addLock(id, howLong); err != nil {
		return Lock{}, errors.New(fmt.Sprintf("cannot lock transaction, %v", err))
	}
	return Lock{
		Id: id,
		unlock: func() {
			ls.unlock(id)
		},
	}, nil
}

func (ls *LocksService) addLock(id string, howLong time.Duration) error {
	expiration := time.Now().Add(howLong)
	return ls.db.Exec("INSERT INTO locks (id, expires) VALUES (@id, @expires);", sql.Named("id", id), sql.Named("expires", expiration)).Error
}

func (ls *LocksService) unlock(id string) {
	ls.db.Exec("DELETE FROM locks WHERE locks.id = @id", sql.Named("id", id))
}

func (ls *LocksService) isLockedAlready(id string) bool {
	var result int
	ls.db.Raw("SELECT count(*) FROM locks WHERE locks.id = @id AND locks.expires > @now", sql.Named("id", id), sql.Named("now", time.Now())).Scan(&result)

	if ls.shouldPerformCleanUpNow() {
		ls.cleanUp()
	}

	return result > 0
}

func (ls *LocksService) cleanUp() {
	ls.db.Exec("DELETE FROM locks WHERE locks.expires < @now", sql.Named("now", time.Now()))
}

func (ls *LocksService) shouldPerformCleanUpNow() bool {
	s1 := rand.NewSource(time.Now().UnixNano())
	r1 := rand.New(s1)

	return r1.Intn(5) == 2 // PN-VI
}

func InitializeModel(db *gorm.DB) error {
	return db.AutoMigrate(&Lock{})
}

func NewService(db *gorm.DB) LocksService {
	return LocksService{db}
}

type Lock struct {
	Id      string
	Expires time.Time
	unlock  func()
}

func (l *Lock) Unlock() {
	l.unlock()
}
