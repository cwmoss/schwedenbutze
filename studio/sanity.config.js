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

export default defineConfig({
  title: 'Schwedenbutze',
  projectId: 'emjk7lsc',
  dataset: 'production',
  plugins: [
    dashboardTool({
      widgets: [
        netlifyWidget({
          title: 'Netlify deploys',
          sites: [
            {
              buildHookId: '60350510667c531882f6f8a1',
              title: 'Sanity Studio',
              name: 'schwedenbutze-studio',
              apiId: '7eed8d89-5e9d-473d-bb95-cb62672fa7cd'
            },
            {
              buildHookId: '60350510b8b2f1125ed3c9db',
              title: 'Blog Website',
              name: 'schwedenbutze',
              apiId: 'a9ad9239-4922-4146-b526-60c12b2823c7'
            }
          ]
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
        documentListWidget({
          title: 'Neueste Inhalte',
          order: '_createdAt desc',
          layout: { width: 'medium' }
        })
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
