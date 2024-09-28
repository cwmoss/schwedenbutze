// sanity.config.js
import { defineConfig } from 'sanity'
import { deskTool } from 'sanity/desk'
import schemas from './schemas/schema'
import { media } from 'sanity-plugin-media'
import { visionTool } from '@sanity/vision'

import deskStructure from './deskStructure'
import { netlifyWidget } from 'sanity-plugin-dashboard-widget-netlify'
import { dashboardTool, projectUsersWidget, projectInfoWidget } from '@sanity/dashboard'
import { documentListWidget } from 'sanity-plugin-dashboard-widget-document-list'
import DeployWebsiteWidget from './plugins/deploy-website3'

export default defineConfig({
  title: 'Schwedenbutze',
  projectId: 'emjk7lsc',
  dataset: 'production',
  plugins: [
    dashboardTool({
      widgets: [
        DeployWebsiteWidget({
          name: 'deploy-website',
          layout: { height: 'auto' },
          options: {
            site: {
              name: 'butzeserver',
              url: process.env.SANITY_STUDIO_DEPLOY_WEBSITE,
              token: process.env.SANITY_STUDIO_DEPLOY_TOKEN,
            },
          },
        }),
        projectInfoWidget({
          data: [
            {
              title: 'GitHub repo',
              value: 'https://github.com/cwmoss/schwedenbutze',
              category: 'Code'
            },
            { title: 'Frontend', value: 'https://schwedenbutze.netlify.app', category: 'apps' }
          ]
        }),
        projectUsersWidget(),
        /*
        documentListWidget({
          title: 'Neueste Inhalte',
          order: '_createdAt desc',
          layout: { width: 'medium' }
        })*/
      ]
    }),

    deskTool({
      structure: deskStructure
    }),
    media(),

    visionTool()
  ],
  schema: {
    types: schemas
  }
})
