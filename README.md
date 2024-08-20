1. Clone the repository from https://github.com/pavlikpavlikpavlik/tech-assessment-laravel-api
2. In the root directory, create an.env file and copy the contents of the .env.example file into it.
3. Go to the src directory and repeat the manipulation with the .env file in the src directory.
4. Install the dependencies: `composer install`.
5. Perform migrations: `php artisan migrate`.
6. Run seed `php artisan db:seed` (this will create a user)
7. Execute `php artisan key:generate`.
8. Go to http://localhost:8080/token and copy the Bearer token.
9. Start Insomnia.
10. Open the Auth tab
    10.1 In the Auth tab, select the “API Key” method.
    10.2 In the “KEY” field, enter “Authorization”.
    10.3 In the “VALUE” field, enter the previously copied Bearer token (Note that the entered token must necessarily contain the prefix “Bearer” space “token” example -
    Bearer 8G8V8V53ODMbRzZzDe1As3nVLFJzrsixuGx5JvKmWMcfc97911 )
11. Open the “Body” tab
    11.1 Add a new line using the (+ Add) button.
    11.2 Enter “file” as the key.
    11.3 Select the type “File” in the selector as the value.
    11.4 Select the file to be uploaded which you want to verify.
12. In the address bar, enter the URL (http://localhost:8080/api/verify) and the POST request type.
13. Click Send. The file will be parsed and a response will be returned to you.

Answer choices coded 400:
* If the request was sent without a file:
```
{
    "error": "No file was uploaded."
}
```

* If the request was sent with a file exceeding 2 mb:
```
{
    "error": {
        "file": [
            "The file failed to upload."
        ]
    }
}      
```

* If you entered an incorrect key in the key field, e.g. files instead of file
```
{
    "error": {
        "file": [
            "The file field is required."
        ]
    }
}    
```

* If the file is not JSON:

```
{
    "error": "File is not a valid JSON."
}
```

* If the file contains a structure other than 
```
{
  "data": {
    "id": "63c79bd9303530645d1cca00",
    "name": "Certificate of Completion",
    "recipient": {
      "name": "Marty McFly",
      "email": "marty.mcfly@gmail.com"
    },
    "issuer": {
      "name": "Accredify",
      "identityProof": {
        "type": "DNS-DID",
        "key": "did:ethr:0x05b642ff12a4ae545357d82ba4f786f3aed84214#controller",
        "location": "ropstore.accredify.io"
      }
    },
    "issued": "2022-12-23T00:00:00+08:00"
  },
  "signature": {
    "type": "SHA3MerkleProof",
    "targetHash": "288f94aadadf486cfdad84b9f4305f7d51eac62db18376d48180cc1dd2047a0e"
  }
}
```

you'll get the message:
```
{
    "error": "One or more elements were not found, file probably has invalid structure."
}
```
or
```
{
    "error": "Critical error: invalid file structure"
}
```

Answer choices with code 200:

* file has been successfully verified:
```
{
    "data": {
        "issuer": "Accredify",
        "result": "verified"
    }
}
```

* No recipient's name or e-mail address:
```
{
    "data": {
        "issuer": "Accredify",
        "result": "invalid_recipient"
    }   
}
```

* There is no issuer data available:
```
{
    "data": {
        "issuer": "Accredify",
        "result": "invalid_issuer"
    }
}
```

* Incorrect signature:
```
{
    "data": {
        "issuer": "Accredify",
        "result": "invalid_signature"
    }
}
```








