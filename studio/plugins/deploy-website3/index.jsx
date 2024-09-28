import React, { useState, useRef } from "react";
import { GrSettingsOption, GrTrigger } from "react-icons/gr";
// import PropTypes from "prop-types";
// import getIt from "get-it";
//import jsonResponse from "get-it/lib/middleware/jsonResponse";
// const observable = require('get-it/lib/middleware/observable')
// import promise from "get-it/lib/middleware/promise";

//import Button from "part:@sanity/components/buttons/default";
//import Spinner from "part:@sanity/components/loading/spinner";
import {DashboardWidgetContainer} from '@sanity/dashboard'
import { Button, Spinner, Container, Heading, Box, Card, Text , Inline} from "@sanity/ui";
import styles from "./deploy-website.css?inline";
// import { DashboardWidget, LayoutConfig } from "@sanity/dashboard";
import { useSecrets, SettingsView } from "@sanity/studio-secrets";

const namespace = "webDeploy";
const pluginConfigKeys = [
  {
    key: "hook",
    title: "URL for hook",
  },
  {
    key: "apiKey",
    title: "Your secret API key",
  },
];

/*
secrets document: secrets.webDeploy
*/

/*
const request = getIt([promise(), jsonResponse()])
request.use(
  observable({
    implementation: require('zen-observable')
  })
)
*/
// const request = getIt();
/*
request.use(
  observable({
    implementation: require('zen-observable')
  })
)
*/

// const [showSettings, setShowSettings] = useState(false)

const deploy = function (secrets, setOutput, cb) {
  const xhr = new XMLHttpRequest();
  console.log("+++ widget options", secrets);

  // setOutput("hello start\n++++\n!!!!")

  xhr.open("POST", secrets.hook, true);
  xhr.setRequestHeader("X-Slft-Deploy", secrets.apiKey);

  xhr.onprogress = function (e) {
    //console.log("progress")
    //console.log(e)

    var outp = e.currentTarget.responseText;
    // console.log(outp)
    setOutput(outp);
  };
  xhr.onreadystatechange = function () {
    if (xhr.readyState === XMLHttpRequest.DONE) {
      // && xhr.status === 200
      cb();
    }
  };
  xhr.send();
};

function DeployWebsite(props) {
  console.log("++++ component START", props)

  const site = props.options.site;

  const { secrets } = useSecrets(namespace);

  console.log("++++ component START2", site, secrets)

  const [showSettings, setShowSettings] = useState(false);
  const [loading, setLoading] = useState(false);
  const [output, setOutput] = useState("");

  const owindow = useRef(null);

  let error = false;

  //console.log(props)

  if (error) {
    return <pre>{JSON.stringify(error, null, 2)}</pre>;
  }
  const spin = <Spinner inline={true} />;

  let do_deploy = function () {
    setLoading(true);
    deploy(secrets, setOutput, function () {
      setLoading(false);
    });
  };

  return (
    <DashboardWidgetContainer header={'Seite aktualisieren'}>
      <Card padding={4}>
      
      <Text  className={styles.header}>
        <Inline padding={[4, 4, 4, 0]}>
        {site.name}
        
          
          <Inline
            onClick={() => {
              setShowSettings(true);
            }}
            title="settings"
            padding={4}
          >
            <GrSettingsOption />
          </Inline>
          {loading ? spin : null}
          </Inline>
      </Text>
      {showSettings && (
        <SettingsView
          namespace={namespace}
          keys={pluginConfigKeys}
          onClose={() => {
            setShowSettings(false);
          }}
        />
      )}
      <Box paddingY={2}>
        <Button bleed color="primary" kind="simple" onClick={do_deploy}>
          <GrTrigger /> publish
        </Button>
        </Box>

      <div ref={owindow} className={styles.content}>
        <div dangerouslySetInnerHTML={{ __html: output }} />
      </div>
      
      </Card>
      
    </DashboardWidgetContainer>
  );
}

/*
export default {
  name: 'deploy-website',
  component: DeployWebsite
}
*/
export default function DeployWebsiteWidget(config) {
  return {
    name: "deploy-website",
    component: () => {
      return <DeployWebsite {...config} />;
    },
    layout: config.layout ?? { width: "medium" },
  };
}