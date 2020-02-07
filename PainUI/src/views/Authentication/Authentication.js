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

import TreeView from "@material-ui/lab/TreeView";
import ExpandMoreIcon from "@material-ui/icons/ExpandMore";
import ChevronRightIcon from "@material-ui/icons/ChevronRight";
import { TreeItem } from "@material-ui/lab";
import Collapse from "@material-ui/core/Collapse";

import BugReport from "@material-ui/icons/BugReport";
import Code from "@material-ui/icons/Code";
import Cloud from "@material-ui/icons/Cloud";
// core components
import Tasks from "components/Tasks/Tasks.js";
import CustomTabs from "components/CustomTabs/CustomTabs.js";

import { bugs, website, server } from "variables/general.js";

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

  let displayedPermitions = [];
  if (state.permitionsLoaded) {
    displayedPermitions = Object.values(state.fetchedPermitions);
    // console.log(state.fetchedPermitions);

    // console.log(displayedPermitions);
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
                    // value="UUIDv4"
                    formControlProps={{
                      fullWidth: true
                    }}
                  />
                </GridItem>
              </GridContainer>
            </CardBody>

            <CardFooter>
              <Button color="primary">Login</Button>
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
                {/* <ul dangerouslySetInnerHTML={{ __html: displayedCollection }}></ul> */}
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
                  {/*  <ul>
                    <li>
                      - Potrzebujemy ekranu logowania, w którym można wpisać
                      TOKEN. TOKEN jest kodem uwierzytelniającym np.
                      a05160d0-dad3-4614-9a1f-a27cdad81606 Jest to forma hasła
                      do aplikacji.
                    </li>
                    <li>
                      - Potrzebujemy widoku do listy tokenów, z możliwością
                      filtrowania według “Tylko aktywne” (domyślnie zaznaczone).
                      Z paginacją, gdyż może ich być set tysięcy, szczególnie
                      nieaktywnych. Na liście potrzebujemy mieć możliwość
                      przejścia do podglądu/edycji oraz możliwość skasowania za
                      potwierdzeniem
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

        <GridItem xs={12} sm={12} md={12}>
          <Card>
            <CardHeader color="primary">
              <h4 className={classes.cardTitleWhite}>Create New Token</h4>
              <p className={classes.cardCategoryWhite}>Complete token:</p>
            </CardHeader>
            <CardBody>
              <GridContainer>
                <GridItem xs={12} sm={12} md={6}>
                  <CustomInput
                    labelText="Token Name"
                    id="first-name"
                    formControlProps={{
                      fullWidth: true
                    }}
                  />
                </GridItem>
                <GridItem xs={12} sm={12} md={6}>
                  <CustomInput
                    labelText="Last Name"
                    id="last-name"
                    formControlProps={{
                      fullWidth: true
                    }}
                  />
                </GridItem>
              </GridContainer>

              <div className={classes.typo}>
                <h3>Permitions:</h3>
              </div>

              <TreeView
                className={classes.root}
                defaultCollapseIcon={<ExpandMoreIcon />}
                defaultExpandIcon={<ChevronRightIcon />}
                // expanded={expanded}
                // onNodeToggle={handleChange}
              >
                <TreeItem nodeId="1" label="Applications">
                  <TreeItem nodeId="2" label="Calendar" />
                  <TreeItem nodeId="3" label="Chrome" />
                  <TreeItem nodeId="4" label="Webstorm" />
                </TreeItem>
                <TreeItem nodeId="5" label="Documents">
                  <TreeItem nodeId="6" label="Material-UI">
                    <TreeItem nodeId="7" label="src">
                      <TreeItem nodeId="8" label="index.js" />
                      <TreeItem nodeId="9" label="tree-view.js" />
                    </TreeItem>
                  </TreeItem>
                </TreeItem>
              </TreeView>
              <CustomTabs
                title="Tasks:"
                headerColor="primary"
                tabs={[
                  {
                    tabName: "Bugs",
                    tabIcon: BugReport,
                    tabContent: (
                      <Tasks
                        checkedIndexes={[0, 3]}
                        tasksIndexes={[0, 1, 2, 3]}
                        tasks={bugs}
                      />
                    )
                  },
                  {
                    tabName: "Website",
                    tabIcon: Code,
                    tabContent: (
                      <Tasks
                        checkedIndexes={[0]}
                        tasksIndexes={[0, 1]}
                        tasks={website}
                      />
                    )
                  },
                  {
                    tabName: "Server",
                    tabIcon: Cloud,
                    tabContent: (
                      <Tasks
                        checkedIndexes={[1]}
                        tasksIndexes={[0, 1, 2]}
                        tasks={server}
                      />
                    )
                  }
                ]}
              />
            </CardBody>

            <CardFooter>
              <Button color="primary">Update Token</Button>
            </CardFooter>
          </Card>
        </GridItem>
      </GridContainer>
    </div>
  );
}
