import {format, parse, parseJSON} from 'date-fns'

export default {
  name: 'info',
  type: 'document',
  title: 'Page Info',
  fields: [
    {
      name: 'page',
      title: 'Page',
      type: 'reference',
      to: [
        {
          type: 'page'
        }
      ]
    },
    {
      name: 'icon',
      type: 'string',
      title: 'Icon',
      description: 'Name of the icon - see https://fontawesome.com/v5/search?o=r&m=free'
    },
    {
      name: 'title',
      type: 'string',
      title: 'Title',
      description: 'Titles should be catchy, descriptive, and not too long'
    },
    {
      name: 'info',
      type: 'string',
      title: 'Info Text',
      description: 'Short text for info box'
    }
  ],
  orderings: [
    {
      name: 'titleAsc',
      title: 'Title asc',
      by: [
        {
          field: 'title',
          direction: 'asc'
        }
      ]
    }
  ],
  preview: {
    select: {
      page: 'page',
      icon: 'icon',
      title: 'title',
    },
    prepare ({title = 'No title', icon = ''}) {
      return {
        title,
      }
    }
  }
}
