package security

import "github.com/riotkit-org/backup-repository/config"

const KindGrantedAccess = "GrantedAccess"

//
// GrantedAccess
//

type GrantedAccessRepository struct {
	config config.ConfigurationProvider
}

func (g GrantedAccessRepository) StoreGeneratedAccessInformation(access GrantedAccess) {
	g.config.StoreDocument(KindGrantedAccess, access)
}
