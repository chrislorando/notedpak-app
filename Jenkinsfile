pipeline {
    agent any

    environment {
        COMPOSE_PROJECT_NAME = 'notedpak-deployment'
        AWS_ACCESS_KEY_ID=credentials('AWS_ACCESS_KEY_ID')
        AWS_SECRET_ACCESS_KEY=credentials('AWS_SECRET_ACCESS_KEY')
        SUPABASE_DB_URL=credentials('SUPABASE_DB_URL')
    }

    stages {
        stage('Clean Previous') {
            steps {
                sh '''
                    # Remove containers
                    docker compose down
                    
                    # Remove project images
                    docker images -q ${COMPOSE_PROJECT_NAME}* | xargs -r docker rmi -f
                    
                    # Clean network
                    docker network prune -f
                '''
            }
        }

        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Build') {
            steps {
                sh 'docker compose build'
            }
        }

        stage('Deploy') {
            steps {
                withCredentials([
                string(credentialsId: 'AWS_ACCESS_KEY_ID', variable: 'AWS_ACCESS_KEY_ID'),
                string(credentialsId: 'AWS_SECRET_ACCESS_KEY', variable: 'AWS_SECRET_ACCESS_KEY'),
                string(credentialsId: 'SUPABASE_DB_URL', variable: 'SUPABASE_DB_URL')
      
                ]) 
                    {
                    sh """
                        docker compose up -d
                        
                        # Wait container ready
                        sleep 10
                        
                        # Run migration and setup Laravel
                        docker compose exec -T app cp .env.example .env
                        docker compose exec -T app php artisan key:generate

                        # Inject secrets
                        echo "AWS_ACCESS_KEY_ID=\$AWS_ACCESS_KEY_ID"
            
                        docker compose exec -T app sh -c 'sed -i "s|^AWS_ACCESS_KEY_ID=.*|AWS_ACCESS_KEY_ID=${AWS_ACCESS_KEY_ID}|" .env'
                        docker compose exec -T app sh -c 'sed -i "s|^AWS_SECRET_ACCESS_KEY=.*|AWS_SECRET_ACCESS_KEY=${AWS_SECRET_ACCESS_KEY}|" .env'
                        docker compose exec -T app sh -c 'sed -i "s|^DB_URL=.*|DB_URL=${SUPABASE_DB_URL}|" .env'

                        # docker compose exec -T app php artisan webpush:vapid
                        
                        # docker compose exec -T app php artisan migrate --seed
                        
                        docker compose exec -T app php artisan optimize:clear
                        docker compose exec -T app php artisan optimize

                        docker compose exec -T app npm run build

                        # docker compose exec -T app php artisan reverb:start &


                    """
                }
            }
        }
    }

    post {
        always {
            sh 'docker compose ps'
            cleanWs()
        }
    }
}