import React, { useState, useEffect } from "react";
// @material-ui/core components
import { makeStyles } from "@material-ui/core/styles";
// core components
import Button from "components/CustomButtons/Button.js";
import GridItem from "components/Grid/GridItem.js";
import GridContainer from "components/Grid/GridContainer.js";
import Card from "components/Card/Card.js";
import CardHeader from "components/Card/CardHeader.js";
import CardBody from "components/Card/CardBody.js";

import CustomInput from "components/CustomInput/CustomInput.js";
import Table from "components/Table/Table.js";

const styles = {
  cardCategoryWhite: {
    color: "rgba(255,255,255,.62)",
    margin: "0",
    fontSize: "14px",
    marginTop: "0",
    marginBottom: "0"
  },
  cardTitleWhite: {
    color: "#FFFFFF",
    marginTop: "0px",
    minHeight: "auto",
    fontWeight: "300",
    fontFamily: "'Roboto', 'Helvetica', 'Arial', sans-serif",
    marginBottom: "3px",
    textDecoration: "none"
  }
};

const useStyles = makeStyles(styles);

export default function Authentication() {
  const classes = useStyles();

  const [fetchedData, updateData] = useState(0);
  const [isLoaded, updateLoaded] = useState(0);
  const [UUIDv4, updateUUIDv4] = useState(
    "67d42b26-8b15-4689-8c78-d24ed15394ef"
  );

  useEffect(() => {
    fetch(
      `https://test-file-repository.riotkit.org/repository/collection?_token=${UUIDv4}`
    )
      .then(res => res.json())
      .then(json => {
        updateData(json);
        updateLoaded(true);
      });
  }, []);

  let displayedData = [];
  if (isLoaded) {
    displayedData = fetchedData.elements.map(element => [
      element.filename,
      element.max_one_backup_version_size,
      element.max_collection_size,
      element.created_at.date
    ]);
    // .join(" ");
  }

  return (
    <div>
      <GridContainer>
        <GridItem xs={12} sm={12} md={12}>
          <Card>
            <CardHeader color="primary">
              <h4 className={classes.cardTitleWhite}>Load Collection</h4>
              <p className={classes.cardCategoryWhite}>
                Collection loaded from UUIDv4: {UUIDv4}
              </p>
            </CardHeader>
            <CardBody>
              <GridContainer>
                {/* <ul dangerouslySetInnerHTML={{ __html: displayedData }}></ul> */}
                <CardBody>
                  <GridContainer>
                    <GridItem xs={12} sm={12} md={6}>
                      <CustomInput
                        labelText="Token"
                        id="Token"
                        // value="UUIDv4"
                        formControlProps={{
                          fullWidth: true
                        }}
                      />
                    </GridItem>
                    <GridItem xs={12} sm={12} md={6}>
                      <Button color="primary">Show Collection</Button>
                    </GridItem>
                  </GridContainer>
                  <Table
                    tableHeaderColor="primary"
                    tableHead={[
                      "FileName",
                      "MaxBackupSize",
                      "MaxCollectionSize",
                      "Created"
                    ]}
                    tableData={displayedData}
                  />
                  {/* <ul>
                    <li>
                      - Potrzebujemy ekranu logowania, w którym można wpisać
                      TOKEN. TOKEN jest kodem uwierzytelniającym np. a05160d0
                    </li>
                    <li>
                      -dad3-4614-9a1f-a27cdad81606 Jest to forma hasła do
                      aplikacji.{" "}
                    </li>
                    <li>
                      - Potrzebujemy widoku do listy tokenów, z możliwością
                      filtrowania według “Tylko aktywne” (domyślnie zaznaczone).
                      Z paginacją, gdyż może ich być set tysięcy, szczególnie
                      nieaktywnych. Na liście potrzebujemy mieć możliwość
                      przejścia do podglądu/edycji oraz możliwość skasowania za
                      potwierdzeniem{" "}
                    </li>
                    <li>
                      - Potrzebujemy możliwości znalezienia tokenu po id aby móc
                      zobaczyć szczegóły i edytować/skasować
                    </li>
                    <li>
                      - Tworzenie nowych tokenów, cały formularz z uprawnieniami
                      i opcjami
                    </li>
                  </ul> */}
                </CardBody>
              </GridContainer>
            </CardBody>
          </Card>
        </GridItem>
      </GridContainer>
    </div>
  );
}
