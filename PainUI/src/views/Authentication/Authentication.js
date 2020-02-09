import React, { useState, useEffect } from "react";
import axios from "axios";
// @material-ui/core components
import { makeStyles } from "@material-ui/core/styles";
// core components
import Button from "components/CustomButtons/Button.js";
import GridItem from "components/Grid/GridItem.js";
import GridContainer from "components/Grid/GridContainer.js";
import Card from "components/Card/Card.js";
import CardHeader from "components/Card/CardHeader.js";
import CardBody from "components/Card/CardBody.js";
import CardFooter from "components/Card/CardFooter.js";
import CreateToken from "components/CreateToken/CreateToken.js";

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

  let [state, updateState] = useState({
    fetchedCollection: {},
    collectionLoaded: false,
    fetchedPermitions: {},
    permitionsLoaded: false,
    displayedPermitions: {},
    tokenInput: "",
    UUIDv4: "67d42b26-8b15-4689-8c78-d24ed15394ef"
  });

  useEffect(() => {
    axios
      .get(
        `https://test-file-repository.riotkit.org/auth/roles?_token=${state.UUIDv4}`
      )
      .then(response => {
        updateState({
          ...state,
          fetchedPermitions: response.data.roles,
          permitionsLoaded: true
        });
      });

    fetch(
      `https://test-file-repository.riotkit.org/repository/collection?_token=${state.UUIDv4}`
    )
      .then(res => res.json())
      .then(json => {
        updateState({
          ...state,
          fetchedCollection: json,
          collectionLoaded: true
        });
      });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  let displayedCollection = [];
  if (state.collectionLoaded) {
    displayedCollection = state.fetchedCollection.elements.map(element => [
      element.filename,
      element.max_one_backup_version_size.toString(),
      element.max_collection_size.toString(),
      element.created_at.date
    ]);
  }

  if (state.permitionsLoaded) {
    state.displayedPermitions = Object.values(state.fetchedPermitions);
  }

  const handleClick = function(e) {
    // updateState({ ...state, tokenInput: document.getElementById("Token").value });
    // console.log(state.tokenInput)
    // console.log(document.getElementById("Token"))
  };

  const handleChange = function(e) {
    // updateState({ ...state, tokenInput: e.target.value });
    // console.log(e)
    // console.log(document.getElementById("Token"))
  }

  return (
    <div>
      <GridContainer>
        <GridItem xs={12} sm={12} md={8}>
          <Card>
            <CardHeader color="primary">
              <h4 className={classes.cardTitleWhite}>Login</h4>
              <p className={classes.cardCategoryWhite}>
                Please provide your token
              </p>
            </CardHeader>
            <CardBody>
              <GridContainer>
                <GridItem xs={12} sm={12} md={12}>
                  <CustomInput
                    labelText="Token"
                    id="Token"
                    value="UUIDv4"
                    defaultValue="UUIDv4"
                    onClick={handleChange()}
                    formControlProps={{
                      fullWidth: true
                    }}
                  />
                </GridItem>
              </GridContainer>
            </CardBody>

            <CardFooter>
              <Button color="primary" onClick={handleClick()}>Login</Button>
            </CardFooter>
          </Card>
        </GridItem>

        <GridItem xs={12} sm={12} md={12}>
          <Card>
            <CardHeader color="primary">
              <h4 className={classes.cardTitleWhite}>Load Collection</h4>
              <p className={classes.cardCategoryWhite}>
                Collection loaded from UUIDv4: {state.UUIDv4}
              </p>
            </CardHeader>
            <CardBody>
              <GridContainer>
                <CardBody>
                  <Table
                    tableHeaderColor="primary"
                    tableHead={[
                      "FileName",
                      "MaxBackupSize",
                      "MaxCollectionSize",
                      "Created"
                    ]}
                    tableData={displayedCollection}
                  />

                </CardBody>
              </GridContainer>
            </CardBody>
          </Card>
        </GridItem>

        <CreateToken displayedPermitions={state.displayedPermitions}/>
      </GridContainer>
    </div>
  );
}
