import { netlifyWidget } from 'sanity-plugin-dashboard-widget-netlify'

export default {
  widgets: [
    {
      name: 'deploy-website',
      layout: { height: 'auto' },
      options: {
        site: {
          name: 'butzeserver',
          url: process.env.SANITY_STUDIO_DEPLOY_WEBSITE,
          token: process.env.SANITY_STUDIO_DEPLOY_TOKEN,
        },
      },
    },
    {
      name: 'project-info',

      options: {
        data: [
          {
            title: 'GitHub repo',
            value: 'https://github.com/cwmoss/schwedenbutze',
            category: 'Code'
          },
          { title: 'Frontend', value: 'https://schwedenbutze.netlify.app', category: 'apps' }
        ]
      }
    },
    { name: 'project-users', layout: { height: 'auto' } },
    /*{
      name: 'document-list',
      options: { title: 'Recent blog posts', order: '_createdAt desc', types: ['post'] },
      layout: { width: 'medium' }
    }*/
  ]
}
